<?php

declare(strict_types=1);

namespace Backbrain\Automapper;

use Backbrain\Automapper\Context\ResolutionContextProvider;
use Backbrain\Automapper\Contract\AutoMapperInterface;
use Backbrain\Automapper\Contract\MapInterface;
use Backbrain\Automapper\Contract\MapperConfigurationInterface;
use Backbrain\Automapper\Contract\MemberInterface;
use Backbrain\Automapper\Contract\NamingConventionInterface;
use Backbrain\Automapper\Contract\ResolutionContextInterface;
use Backbrain\Automapper\Contract\ResolutionContextProviderInterface;
use Backbrain\Automapper\Exceptions\MapperException;
use Backbrain\Automapper\Helper\Func;
use Backbrain\Automapper\Helper\Property;
use Backbrain\Automapper\Model\Map;
use Backbrain\Automapper\Model\Member;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PhpStan\NameScope;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\PropertyInfo\Util\PhpStanTypeHelper;
use Symfony\Component\TypeInfo\TypeContext\TypeContext;

abstract class BaseMapper implements AutoMapperInterface, LoggerAwareInterface
{
    protected LoggerInterface $logger;

    protected PropertyAccessorInterface $propertyAccessor;

    protected PropertyInfoExtractorInterface $propertyInfoExtractor;

    protected ResolutionContextProviderInterface $resolutionContextProvider;

    protected ?ContainerInterface $container = null;

    /**
     * @var array<string, array<string, Map>>
     */
    protected array $maps = [];

    public function __construct(
        MapperConfigurationInterface $mapperConfiguration,
        ?CacheItemPoolInterface $cacheItemPool = null,
        ?LoggerInterface $logger = null,
        ?ResolutionContextProviderInterface $resolutionContextProvider = null,
        ?ContainerInterface $container = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
        $this->container = $container;
        $this->propertyAccessor = Property::newPropertyAccessor($cacheItemPool);
        $this->propertyInfoExtractor = Property::newPropertyInfoExtractor($cacheItemPool);
        $this->resolutionContextProvider = $resolutionContextProvider ?? new ResolutionContextProvider();

        $this->applyMaps($mapperConfiguration->getMaps());
    }

    /**
     * @param MapInterface[] $maps
     */
    private function applyMaps(array $maps): void
    {
        /* @var array<string, array<string, Map>> $mapping */
        $this->maps = [];
        foreach ($maps as $map) {
            if (!isset($this->maps[$map->getSourceType()])) {
                $this->maps[$this->canonicalize($map->getSourceType())] = [];
            }

            $defaultTypeFactory = $map->getTypeFactory();
            if ($this->canInstantiate($map->getDestinationType())) {
                $defaultTypeFactory = Func::typeFactoryFromFn(
                    $defaultTypeFactory ?? fn (mixed $source, ResolutionContextInterface $context) => $this->createA(
                        $map->getDestinationType()
                    )
                );
            }

            $this->maps[$this->canonicalize($map->getSourceType())][$this->canonicalize(
                $map->getDestinationType()
            )] = Map::from($map)
                ->withTypeFactory($defaultTypeFactory);
        }

        foreach ($this->maps as $sourceType => $destinationTypes) {
            foreach ($destinationTypes as $destinationType => $map) {
                $this->maps[$sourceType][$destinationType] = $this->resolveMap($map, $this->maps);
            }
        }

        foreach ($this->maps as $destinationTypes) {
            foreach ($destinationTypes as $map) {
                foreach ($map->getIncludeMaps() as $targetMap) {
                    $targetSrcType = $targetMap->getSourceType();
                    $targetDstType = $targetMap->getDestinationType();

                    $targetMap = $this->mustGetMap($this->maps, $targetSrcType, $targetDstType);
                    $targetMap = Map::mergeMembers($map, $targetMap);

                    $this->maps[$targetMap->getSourceType()][$targetMap->getDestinationType()] = $targetMap;
                }
            }
        }
    }

    /**
     * @param array<string, array<string, Map>> $maps
     * @param string[]                          $stack
     */
    private function resolveMap(Map $map, array $maps, array $stack = []): Map
    {
        if (in_array($map->getDestinationType(), $stack, true)) {
            throw MapperException::newCircularDependencyException($stack, $map->getSourceType(), $map->getDestinationType());
        }

        $stack[] = $map->getDestinationType();

        $result = Map::from($map);
        $mappedBy = $map->getAs();
        if (null !== $mappedBy) {
            $mappedByMap = $this->resolveMap($this->mustGetMap($maps, $map->getSourceType(), $mappedBy), $maps, $stack);
            $result = Map::merge($result, $mappedByMap)
                ->withMappedBy(null)
                ->withDestinationType($map->getDestinationType());
        }

        $baseMap = $map->getIncludeBaseMap();
        if (null !== $baseMap) {
            $baseMap = $this->resolveMap(
                $this->mustGetMap($maps, $baseMap->getSourceType(), $baseMap->getDestinationType()),
                $maps,
                $stack
            );
            $result = Map::mergeMembers($result, $baseMap)
                ->withDestinationType($map->getDestinationType())
                ->withSourceType($map->getSourceType());
        }

        return $result;
    }

    /**
     * @param array<string, array<string, Map>> $maps
     */
    protected function mustGetMap(array $maps, string $sourceType, string $destinationType): Map
    {
        $map = $this->getMap($maps, $sourceType, $destinationType);
        if (null === $map) {
            throw MapperException::newMissingMapException($this->canonicalize($sourceType), $this->canonicalize($destinationType));
        }

        return $map;
    }

    /**
     * @param array<string, array<string, Map>> $maps
     */
    protected function getMap(array $maps, string $sourceType, string $destinationType): ?Map
    {
        $sourceType = $this->canonicalize($sourceType);
        $destinationType = $this->canonicalize($destinationType);

        if (isset($maps[$sourceType][$destinationType])) {
            return $maps[$sourceType][$destinationType];
        }

        $this->logger->debug('No map found for source and destination type.', [
            'sourceType' => $sourceType,
            'destinationType' => $destinationType,
        ]);

        return null;
    }

    /**
     * @return MemberInterface[]
     */
    protected function membersFor(MapInterface $map): array
    {
        $members = [];
        $properties = $this->propertyInfoExtractor->getProperties($map->getDestinationType()) ?? [];

        foreach ($properties as $propertyName) {
            $destPropertyName = $this->translatePropertyName(
                $map,
                $propertyName,
                $map->getDestinationMemberNamingConvention()
            );

            $members[$destPropertyName] = $this->memberFor($map, $destPropertyName);
        }

        // explicit member definition have precedence over source properties
        foreach ($map->getMembers() as $member) {
            $members[$member->getDestinationProperty()] = $member;
        }

        return array_values($members);
    }

    protected function memberFor(MapInterface $map, string $propertyName): MemberInterface
    {
        foreach ($map->getMembers() as $member) {
            if ($member->getDestinationProperty() === $propertyName) {
                return $member;
            }
        }

        return new Member($propertyName);
    }

    protected function translatePropertyName(
        MapInterface $map,
        string $propertyName,
        ?NamingConventionInterface $namingConvention = null,
    ): string {
        if (null === $namingConvention || $map->getSourceMemberNamingConvention(
        ) === $map->getDestinationMemberNamingConvention()) {
            return $propertyName;
        }

        return $namingConvention->translate($propertyName);
    }

    /**
     * @return array{mixed, bool}
     */
    protected function propertyRead(object $source, string $propertyPath): array
    {
        $sourceClass = get_class($source);
        if (!$this->propertyAccessor->isReadable($source, $propertyPath)) {
            $this->logger->debug('Source property is not readable.', [
                'sourceClass' => $sourceClass,
                'propertyPath' => $propertyPath,
            ]);

            return [null, false];
        }

        return [$this->propertyAccessor->getValue($source, $propertyPath), true];
    }

    protected function propertyWrite(object $target, string $propertyName, mixed $value): bool
    {
        $destinationClass = get_class($target);

        if (!$this->propertyAccessor->isWritable($target, $propertyName)) {
            $this->logger->warning('Destination property is not writable.', [
                'destinationClass' => $destinationClass,
                'property' => $propertyName,
            ]);

            return false;
        }

        $this->propertyAccessor->setValue($target, $propertyName, $value);
        $this->logger->debug('Mapped property from source to destination.', [
            'destinationClass' => $destinationClass,
            'property' => $propertyName,
            'value' => $value,
        ]);

        return true;
    }

    /**
     * @return Type[]
     */
    protected function parseTypeExpr(string $type): array
    {
        // ensure that the type is a valid PHPStan type expression
        // it should only contain [a-zA-Z0-9_|\\<>]+
        $regex = '/^[\\\a-zA-Z0-9_|<>\\[\\],]+$/';
        if (!preg_match($regex, $type)) {
            throw MapperException::newIllegalTypeException($type);
        }

        [$phpDocParser, $tokens] = Helper\PhpDocParserFactory::create($type);

        $phpDocNode = $phpDocParser->parse($tokens); // PhpDocNode
        $paramTags = $phpDocNode->getReturnTagValues();

        if (!isset($paramTags[0])) {
            throw MapperException::newIllegalTypeException($type);
        }

        // This is a workaround for the missing public API in Symfony's PropertyInfo component
        // Mind future changes in Symfony's PropertyInfo component
        if (class_exists(NameScope::class)) {
            // @phpstan-ignore-next-line
            return (new PhpStanTypeHelper())->getTypes($paramTags[0], new NameScope(\stdClass::class, '', []));
        }

        // @phpstan-ignore-next-line
        return (new PhpStanTypeHelper())->getTypes($paramTags[0], new TypeContext(\stdClass::class, \stdClass::class));
    }

    protected function canonicalize(string $type): string
    {
        // we want to have no whitespaces
        $type = ltrim(str_replace(' ', '', str_replace('\\\\', '\\', trim($type))), '\\');

        // TODO find a better way to handle Proxies\\__CG__\\ namespace and make it somehow configurable
        // strip Proxies\\__CG__\\ prefix from type
        return preg_replace('/^Proxies\\\__CG__\\\/', '', $type) ?? $type;
    }

    protected function isType(mixed $value, string $expectedType): bool
    {
        return Helper\Type::toString($value) === $expectedType || ((is_object($value) || is_string($value)) && is_a(
            $value,
            $expectedType,
            true
        ));
    }

    protected function assertType(mixed $value, string $expectedType): void
    {
        if (!$this->isType($value, $expectedType)) {
            throw MapperException::newUnexpectedTypeException($expectedType, $value);
        }
    }

    protected function createA(string $type): object
    {
        if (!class_exists($type)) {
            throw MapperException::newDestinationClassNotFoundException($type);
        }

        if (!$this->canInstantiate($type)) {
            throw MapperException::newInstantiationFailedException($type);
        }

        return new $type();
    }

    protected function canInstantiate(string $type): bool
    {
        return class_exists($type) && (new \ReflectionClass($type))->isInstantiable();
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
