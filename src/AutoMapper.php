<?php

declare(strict_types=1);

namespace Backbrain\Automapper;

use Backbrain\Automapper\Context\MappingContext;
use Backbrain\Automapper\Contract\MapInterface;
use Backbrain\Automapper\Contract\MemberInterface;
use Backbrain\Automapper\Contract\ResolutionContextInterface;
use Backbrain\Automapper\Exceptions\ContextAwareMapperException;
use Backbrain\Automapper\Exceptions\MapperException;
use Backbrain\Automapper\Helper\TypeTransformer;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\UnionType;

class AutoMapper extends BaseMapper
{
    /**
     * Maps a source object to a destination type.
     *
     * @template T of object
     *
     * @param class-string<T> $destinationType
     *
     * @return T
     */
    public function map(object $source, string $destinationType): object
    {
        $ctx = MappingContext::root($source, $destinationType);

        return $this->doMap($source, $destinationType, $ctx);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $destinationType
     *
     * @return T
     */
    private function doMap(object $source, string $destinationType, MappingContext $ctx): object
    {
        try {
            $sourceType = get_class($source);
            $sourceValue = $source;

            $map = $this->mustGetMap($this->maps, $sourceType, $destinationType);
            $ctx = $ctx->withAppliedMap($map);
            $destinationValue = $this->newInstanceOf($map, $sourceValue, $destinationType, $ctx);
            if (!$destinationValue instanceof $destinationType) {
                throw MapperException::newUnexpectedTypeException($destinationType, $destinationValue, $ctx);
            }

            $this->mapType($map, $sourceValue, $destinationValue, $ctx);

            return $destinationValue;
        } catch (MapperException $e) {
            throw $this->rethrowWithContext($e, $ctx);
        }
    }

    public function mapIterable(iterable $source, string $destinationType): iterable
    {
        $ctx = MappingContext::root($source, $destinationType);
        try {
            $type = $this->parseTypeExpr($destinationType);
            $types = $type instanceof UnionType ? $type->getTypes() : [$type];

            foreach ($types as $dType) {
                $destTypeStr = Helper\Type::toString($dType);
                $ctxLoop = $ctx->withDestTypes([$destTypeStr]);
                [$value, $ok] = $this->handleMapping($dType, $source, $ctxLoop);
                if ($ok) {
                    if (!is_iterable($value)) {
                        throw new \LogicException('Type is not a collection');
                    }

                    return $value;
                }
            }

            throw MapperException::newIllegalTypeException($destinationType, $ctx);
        } catch (MapperException $e) {
            throw $this->rethrowWithContext($e, $ctx);
        }
    }

    public function mutate(object $source, object $destination): void
    {
        $destinationType = get_class($destination);
        $ctx = MappingContext::root($source, $destinationType);
        try {
            $sourceType = get_class($source);
            $sourceValue = $source;

            $map = $this->mustGetMap($this->maps, $sourceType, $destinationType);
            $ctx = $ctx->withAppliedMap($map);
            $destinationValue = $destination;

            $this->mapType($map, $sourceValue, $destinationValue, $ctx);
        } catch (MapperException $e) {
            throw $this->rethrowWithContext($e, $ctx);
        }
    }

    private function mapType(MapInterface $map, mixed $srcValue, mixed $destValue, MappingContext $ctx): void
    {
        if (!is_object($srcValue) || !is_object($destValue)) {
            throw new \LogicException('Not implemented');
        }

        if ($map->getBeforeMap()) {
            $map->getBeforeMap()->process(
                $srcValue,
                $destValue,
                $this->newResolutionContext(map: $map, source: $srcValue)
            );
        }

        foreach ($this->membersFor($map) as $member) {
            [$sourcePropertyValue, $ok] = $this->memberSourceValueFor($map, $member, $srcValue, $ctx);
            if (!$ok) {
                $this->logger->warning('Cannot access source property.', [
                    'source' => $srcValue,
                    'destination' => $destValue,
                    'member' => $member->getDestinationProperty(),
                ]);

                continue;
            }

            if (null !== $member->getCondition() && !$member->getCondition()(
                $srcValue,
                $this->newResolutionContext(map: $map, source: $srcValue)
            )) {
                $this->logger->debug('Member condition returned false.', [
                    'source' => $srcValue,
                    'destination' => $destValue,
                    'member' => $member->getDestinationProperty(),
                ]);

                continue;
            }

            $this->memberDestinationValuePut($member, $destValue, $sourcePropertyValue, $ctx);
        }

        if ($map->getAfterMap()) {
            $map->getAfterMap()->process(
                $srcValue,
                $destValue,
                $this->newResolutionContext(map: $map, source: $srcValue)
            );
        }
    }

    /**
     * @param Type[] $types
     */
    private function handleValue(mixed $value, array $types, MappingContext $ctx): mixed
    {
        if (count($types) < 1) {
            return $value;
        }

        $failedTypes = [];
        foreach ($types as $type) {
            [$targetValue, $ok] = $this->handleMapping($type, $value, $ctx);
            if ($ok) {
                return $targetValue;
            }

            $failedTypes[] = Helper\Type::toString($type);
        }

        throw MapperException::newMissingMapsException(Helper\Type::toString($value), $failedTypes, $ctx);
    }

    /**
     * @return array{mixed, bool}
     */
    private function handleMapping(Type $destPropertyInfoType, mixed $value, MappingContext $ctx): array
    {
        // Handle UnionType by trying to map to each type in the union
        if ($destPropertyInfoType instanceof UnionType) {
            foreach ($destPropertyInfoType->getTypes() as $type) {
                [$targetValue, $ok] = $this->handleMapping($type, $value, $ctx);
                if ($ok) {
                    return [$targetValue, true];
                }
            }

            return [null, false];
        }

        $srcType = Helper\Type::toString($value);

        if ((null === $value && Helper\Type::isNullable($destPropertyInfoType)) || 'mixed' === Helper\Type::getBuiltinType($destPropertyInfoType)) {
            return [$value, true];
        }

        $destType = Helper\Type::getBuiltinType($destPropertyInfoType);
        if ('object' === Helper\Type::getBuiltinType($destPropertyInfoType)) {
            $destType = Helper\Type::getClassName($destPropertyInfoType);
            if (null === $destType) {
                throw new \LogicException('Cannot reflect class name for object');
            }
        }

        $srcType = $this->canonicalize($srcType);
        $destType = $this->canonicalize($destType);

        if (Helper\Type::isCollection($destPropertyInfoType)) {
            $collection = $this->newCollectionFor($destPropertyInfoType, $value, $ctx);

            foreach (is_iterable($value) ? $value : [$value] as $itemKey => $itemValue) {
                // @phpstan-ignore-next-line
                $targetValue = $this->handleValue($itemValue, Helper\Type::getCollectionValueTypes($destPropertyInfoType), $ctx);
                // @phpstan-ignore-next-line
                $targetKey = $this->handleValue($itemKey, Helper\Type::getCollectionKeyTypes($destPropertyInfoType), $ctx);

                // @phpstan-ignore-next-line
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
            $destClass = $map->getDestinationType();
            if (!class_exists($destClass) && !interface_exists($destClass)) {
                throw MapperException::newDestinationClassNotFoundException($destClass, $ctx);
            }
            /** @var class-string<object> $destClass */
            $targetValue = $this->doMap($value, $destClass, $ctx); // keep context

            return [$targetValue, true];
        }

        return [null, false];
    }

    private function memberDestinationValuePut(MemberInterface $member, object $dest, mixed $value, MappingContext $ctx): void
    {
        if ($member->isIgnored()) {
            return;
        }

        // @phpstan-ignore-next-line
        if (method_exists($this->propertyInfoExtractor, 'getType')) {
            $type = $this->propertyInfoExtractor->getType(get_class($dest), $member->getDestinationProperty());
        } else {
            // @phpstan-ignore-next-line
            $types = $this->propertyInfoExtractor->getTypes(get_class($dest), $member->getDestinationProperty());
            $type = $types ? TypeTransformer::toTypeInfoType($types) : null;
        }

        if (null === $type) {
            $failedTypes = [];
            throw MapperException::newMissingMapsException(Helper\Type::toString($value), $failedTypes, $ctx);
        }

        $types = $type instanceof UnionType ? $type->getTypes() : [$type];

        $failedTypes = [];
        /** @var Type $destPropertyInfoType */
        foreach ($types as $destPropertyInfoType) {
            $failedTypes[] = Helper\Type::toString($destPropertyInfoType);
            [$targetValue, $ok] = $this->handleMapping($destPropertyInfoType, $value, $ctx);

            if (!$ok) {
                $this->logger->info(sprintf('Type mapping failed for member "%s"', $member->getDestinationProperty()), [
                    'sourceType' => Helper\Type::toString($value),
                    'destinationType' => Helper\Type::getBuiltinType($destPropertyInfoType),
                ]);

                continue;
            }

            $this->propertyWrite($dest, $member->getDestinationProperty(), $targetValue);

            return;
        }

        throw MapperException::newMissingMapsException(Helper\Type::toString($value), $failedTypes, $ctx);
    }

    /**
     * @return array{mixed, bool}
     */
    private function memberSourceValueFor(MapInterface $map, MemberInterface $member, object $source, MappingContext $ctx): array
    {
        $valueProvider = $member->getValueProvider();
        if (null !== $valueProvider) {
            return [$valueProvider->resolve($source, $this->newResolutionContext($map, $member, $source)), true];
        }

        $propertyName = $this->translatePropertyName(
            $map,
            $member->getDestinationProperty(),
            $map->getSourceMemberNamingConvention()
        );

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
    private function newCollectionFor(Type $destType, mixed $srcValue, MappingContext $ctx): \ArrayAccess|array
    {
        if (!Helper\Type::isCollection($destType)) {
            throw new \LogicException('Type is not a collection');
        }

        if (Helper\Type::ARRAY == Helper\Type::getBuiltinType($destType)) {
            return [];
        }

        $destClassName = Helper\Type::getClassName($destType);
        if (null === $destClassName) {
            return [];
        }

        $srcTypeString = Helper\Type::toString($srcValue);
        $map = $this->getMap($this->maps, $srcTypeString, $destClassName);

        if (null !== $map) {
            $collection = $this->newInstanceOf($map, $srcValue, $destClassName, $ctx);
            if (!$collection instanceof \ArrayAccess) {
                throw MapperException::newCollectionNotWriteableException(Helper\Type::getClassName($destType) ?? Helper\Type::getBuiltinType($destType), $ctx);
            }

            return $collection;
        }

        if (interface_exists($destClassName)) {
            throw MapperException::newInstantiationFailedException($destClassName, $srcTypeString, $ctx);
        }

        if (!class_exists($destClassName)) {
            throw MapperException::newDestinationClassNotFoundException($destClassName, $ctx);
        }

        $collection = new ($destClassName)();
        if (!$collection instanceof \ArrayAccess) {
            throw MapperException::newCollectionNotWriteableException(Helper\Type::getClassName($destType) ?? Helper\Type::getBuiltinType($destType), $ctx);
        }

        return $collection;
    }

    private function newInstanceOf(MapInterface $map, mixed $srcValue, string $destType, MappingContext $ctx): object
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

    private function rethrowWithContext(MapperException $e, MappingContext $ctx): MapperException
    {
        return ContextAwareMapperException::fromMapperException($e, $ctx);
    }

    private function newResolutionContext(
        ?MapInterface $map = null,
        ?MemberInterface $member = null,
        mixed $source = null,
    ): ResolutionContextInterface {
        return $this->resolutionContextProvider->get(
            autoMapper: $this,
            map: $map,
            member: $member,
            source: $source,
        );
    }
}
