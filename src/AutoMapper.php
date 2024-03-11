<?php

declare(strict_types=1);

namespace Backbrain\Automapper;

use Backbrain\Automapper\Context\ResolutionContext;
use Backbrain\Automapper\Contract\MapInterface;
use Backbrain\Automapper\Contract\MemberInterface;
use Backbrain\Automapper\Exceptions\MapperException;
use Symfony\Component\PropertyInfo\Type;

class AutoMapper extends BaseMapper
{
    public function map(object $source, string $destinationType): object
    {
        $sourceType = get_class($source);
        $sourceValue = $source;

        $map = $this->mustGetMap($this->maps, $sourceType, $destinationType);
        $destinationValue = $this->newInstanceOf($map, $sourceValue, $destinationType);
        if (!$destinationValue instanceof $destinationType) {
            throw MapperException::newUnexpectedTypeException($destinationType, $destinationValue);
        }

        $this->mapType($map, $sourceValue, $destinationValue);

        return $destinationValue;
    }

    public function mapIterable(iterable $source, string $destinationType): iterable
    {
        foreach ($this->parseTypeExpr($destinationType) as $dType) {
            [$value, $ok] = $this->handleMapping($dType, $source);
            if ($ok) {
                if (!is_iterable($value)) {
                    throw new \LogicException('Type is not a collection');
                }

                return $value;
            }
        }

        throw MapperException::newIllegalTypeException($destinationType);
    }

    public function mutate(object $source, object $destination): void
    {
        $sourceType = get_class($source);
        $sourceValue = $source;

        $destinationType = get_class($destination);

        $map = $this->mustGetMap($this->maps, $sourceType, $destinationType);
        $destinationValue = $destination;

        $this->mapType($map, $sourceValue, $destinationValue);
    }

    private function mapType(MapInterface $map, mixed $srcValue, mixed $destValue): void
    {
        if (!is_object($srcValue) || !is_object($destValue)) {
            throw new \LogicException('Not implemented');
        }

        if ($map->getBeforeMap()) {
            $map->getBeforeMap()->process($srcValue, $destValue, $this->newResolutionContext(map: $map, source: $srcValue));
        }

        foreach ($this->membersFor($map) as $member) {
            [$sourcePropertyValue, $ok] = $this->memberSourceValueFor($map, $member, $srcValue);
            if (!$ok) {
                $this->logger->warning('Cannot access source property.', [
                    'source' => $srcValue,
                    'destination' => $destValue,
                    'member' => $member->getDestinationProperty(),
                ]);

                continue;
            }

            if (null !== $member->getCondition() && !$member->getCondition()($srcValue)) {
                $this->logger->debug('Member condition returned false.', [
                    'source' => $srcValue,
                    'destination' => $destValue,
                    'member' => $member->getDestinationProperty(),
                ]);

                continue;
            }

            $this->memberDestinationValuePut($member, $destValue, $sourcePropertyValue);
        }

        if ($map->getAfterMap()) {
            $map->getAfterMap()->process($srcValue, $destValue, $this->newResolutionContext(map: $map, source: $srcValue));
        }
    }

    /**
     * @param Type[] $types
     */
    private function handleValue(mixed $value, array $types): mixed
    {
        if (count($types) < 1) {
            return $value;
        }

        $failedTypes = [];
        foreach ($types as $type) {
            [$targetValue, $ok] = $this->handleMapping($type, $value);
            if ($ok) {
                return $targetValue;
            }

            $failedTypes[] = Helper\Type::toString($type);
        }

        throw MapperException::newMissingMapsException(Helper\Type::toString($value), ...$failedTypes);
    }

    /**
     * @return array{mixed, bool}
     */
    private function handleMapping(Type $destPropertyInfoType, mixed $value): array
    {
        $srcType = Helper\Type::toString($value);
        if ((null === $value && $destPropertyInfoType->isNullable()) || 'mixed' === $destPropertyInfoType->getBuiltinType()) {
            return [$value, true];
        }

        $destType = $destPropertyInfoType->getBuiltinType();
        if ('object' === $destPropertyInfoType->getBuiltinType()) {
            $destType = $destPropertyInfoType->getClassName();
            if (null === $destType) {
                throw new \LogicException('Cannot reflect class name for object');
            }
        }

        $srcType = $this->canonicalize($srcType);
        $destType = $this->canonicalize($destType);

        if ($destPropertyInfoType->isCollection()) {
            $collection = $this->newCollectionFor($destPropertyInfoType, $value);

            foreach (is_iterable($value) ? $value : [$value] as $itemKey => $itemValue) {
                $targetValue = $this->handleValue($itemValue, $destPropertyInfoType->getCollectionValueTypes());
                $targetKey = $this->handleValue($itemKey, $destPropertyInfoType->getCollectionKeyTypes());

                $collection[$targetKey] = $targetValue;
            }

            return [$collection, true];
        }

        if ($destType === $srcType) {
            return [$value, true];
        }

        $map = $this->getMap($this->maps, $srcType, $destType);
        if (null === $map) {
            return [null, false];
        }

        $converter = $map->getTypeConverter();
        if (null !== $converter) {
            $targetValue = $converter->convert($value, $this->newResolutionContext(map: $map, source: $value));
            $this->assertType($targetValue, $destType);

            return [$targetValue, true];
        }

        if (is_object($value)) {
            $targetValue = $this->map($value, $map->getDestinationType()); // @phpstan-ignore-line

            return [$targetValue, true];
        }

        return [null, false];
    }

    private function memberDestinationValuePut(MemberInterface $member, object $dest, mixed $value): void
    {
        if ($member->isIgnored()) {
            return;
        }

        $destPropertyInfoTypes = $this->propertyInfoExtractor->getTypes(get_class($dest), $member->getDestinationProperty()) ?? [];

        $failedTypes = [];
        foreach ($destPropertyInfoTypes as $destPropertyInfoType) {
            $failedTypes[] = Helper\Type::toString($destPropertyInfoType);
            [$targetValue, $ok] = $this->handleMapping($destPropertyInfoType, $value);

            if (!$ok) {
                $this->logger->info(sprintf('Type mapping failed for member "%s"', $member->getDestinationProperty()), [
                    'sourceType' => Helper\Type::toString($value),
                    'destinationType' => $destPropertyInfoType->getBuiltinType(),
                ]);

                continue;
            }

            $this->propertyWrite($dest, $member->getDestinationProperty(), $targetValue);

            return;
        }

        throw MapperException::newMissingMapsException(Helper\Type::toString($value), ...$failedTypes);
    }

    /**
     * @return array{mixed, bool}
     */
    private function memberSourceValueFor(MapInterface $map, MemberInterface $member, object $source): array
    {
        $valueProvider = $member->getValueProvider();
        if (null !== $valueProvider) {
            return [$valueProvider->resolve($source), true];
        }

        $propertyName = $this->translatePropertyName($map, $member->getDestinationProperty(), $map->getSourceMemberNamingConvention());

        [$value, $ok] = $this->propertyRead($source, $propertyName);
        if (!$ok) {
            return [null, false];
        }

        $value = $value ?? $member->getNullSubstitute();

        return [$value, true];
    }

    /**
     * @return \ArrayAccess<mixed,mixed>|array<mixed>
     */
    private function newCollectionFor(Type $destType, mixed $srcValue): \ArrayAccess|array
    {
        if (!$destType->isCollection()) {
            throw new \LogicException('Type is not a collection');
        }

        if (Type::BUILTIN_TYPE_ARRAY == $destType->getBuiltinType()) {
            return [];
        }

        $destClassName = $destType->getClassName();
        if (null === $destClassName) {
            return [];
        }

        $srcTypeString = Helper\Type::toString($srcValue);
        $map = $this->getMap($this->maps, $srcTypeString, $destClassName);

        if (null !== $map) {
            $collection = $this->newInstanceOf($map, $srcValue, $destClassName);
            if (!$collection instanceof \ArrayAccess) {
                throw MapperException::newCollectionNotWriteableException($destType->getClassName() ?? $destType->getBuiltinType());
            }

            return $collection;
        }

        if (interface_exists($destClassName)) {
            throw MapperException::newInstantiationFailedException($destClassName, $srcTypeString);
        }

        if (!class_exists($destClassName)) {
            throw MapperException::newDestinationClassNotFoundException($destClassName);
        }

        $collection = new ($destClassName)();
        if (!$collection instanceof \ArrayAccess) {
            throw MapperException::newCollectionNotWriteableException($destType->getClassName() ?? $destType->getBuiltinType());
        }

        return $collection;
    }

    private function newInstanceOf(MapInterface $map, mixed $srcValue, string $destType): object
    {
        $this->logger->debug(sprintf('Creating new instance of "%s"', $destType), [
            'destinationType' => $destType,
            'sourceType' => Helper\Type::toString($srcValue),
        ]);

        $factory = $map->getTypeFactory();
        if (null !== $factory) {
            $instance = $factory->create($srcValue, $this->newResolutionContext(map: $map, source: $srcValue));
            $this->assertType($instance, $destType);

            return $instance;
        }

        return $this->createA($destType);
    }

    private function newResolutionContext(?MapInterface $map = null, ?MemberInterface $member = null, mixed $source = null): ResolutionContext
    {
        return new ResolutionContext(
            autoMapper: $this,
            map: $map,
            member: $member,
            source: $source,
        );
    }
}
