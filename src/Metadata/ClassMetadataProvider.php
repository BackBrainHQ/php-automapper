<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Metadata;

use Backbrain\Automapper\Contract\Attributes\Condition;
use Backbrain\Automapper\Contract\Attributes\ConstructUsing;
use Backbrain\Automapper\Contract\Attributes\Ignore;
use Backbrain\Automapper\Contract\Attributes\MapFrom;
use Backbrain\Automapper\Contract\Attributes\MapTo;
use Backbrain\Automapper\Contract\Attributes\NamingConvention;
use Backbrain\Automapper\Contract\Attributes\NullSubstitute;
use Backbrain\Automapper\Contract\Builder\Map;
use Backbrain\Automapper\Contract\Builder\Options;
use Backbrain\Automapper\Contract\MappingActionInterface;
use Backbrain\Automapper\Contract\Metadata\AttributeMetadataProviderInterface;
use Backbrain\Automapper\Contract\Metadata\ClassMetadataProviderInterface;
use Backbrain\Automapper\Contract\ProfileInterface;
use Backbrain\Automapper\Contract\ResolutionContextInterface;
use Backbrain\Automapper\Contract\TypeConverterInterface;
use Backbrain\Automapper\Contract\ValueResolverInterface;
use Backbrain\Automapper\Helper\Property;
use Backbrain\Automapper\Profiles\AnonymousProfile;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Webmozart\Assert\Assert;

/**
 * @internal This class is not part of the public API and may change at any time!
 */
class ClassMetadataProvider implements ClassMetadataProviderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private CacheItemPoolInterface $cacheItemPool;

    private ExpressionLanguage $expressionLanguage;

    private PropertyInfoExtractorInterface $propertyInfo;

    private AttributeMetadataProviderInterface $attributeMetadataProvider;

    private ?ContainerInterface $container;

    public function __construct(
        ?LoggerInterface $logger = null,
        ?CacheItemPoolInterface $cacheItemPool = null,
        ?ExpressionLanguage $expressionLanguage = null,
        ?PropertyInfoExtractorInterface $propertyInfo = null,
        ?AttributeMetadataProviderInterface $attributeMetadataProvider = null,
        ?ContainerInterface $container = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
        $this->cacheItemPool = $cacheItemPool ?? new ArrayAdapter();
        $this->expressionLanguage = $expressionLanguage ?? new ExpressionLanguage($this->cacheItemPool);
        $this->propertyInfo = $propertyInfo ?? Property::newPropertyInfoExtractor($this->cacheItemPool);
        $this->attributeMetadataProvider = $attributeMetadataProvider ?? new AttributeMetadataProvider();
        $this->container = $container;
    }

    /**
     * @param class-string $className
     *
     * @return ProfileInterface[]
     */
    public function getProfiles(string $className): array
    {
        if (!class_exists($className)) {
            throw new \InvalidArgumentException(sprintf('Class %s does not exist', $className));
        }

        $sourceReflectionClass = new \ReflectionClass($className);

        $profiles = [];

        $mapAttrs = $this->attributeMetadataProvider->getClassAttrs($sourceReflectionClass, MapTo::class);
        $sourceNamingConventionAttr = $this->attributeMetadataProvider->getClassAttr(
            $sourceReflectionClass,
            NamingConvention::class
        );

        foreach ($mapAttrs as $mapAttr) {
            $profile = $this->handleMapTo($mapAttr, $className, $sourceNamingConventionAttr);

            $profiles[] = $profile;
        }

        return $profiles;
    }

    private function handleMapTo(
        MapTo $sourceMapAttr,
        string $sourceClassName,
        ?NamingConvention $sourceNamingConventionAttr,
    ): ProfileInterface {
        $destClassName = $sourceMapAttr->getDest();
        if (!class_exists($sourceClassName)) {
            throw new \InvalidArgumentException(sprintf('Source class %s does not exist', $sourceClassName));
        }

        if (!class_exists($destClassName)) {
            throw new \InvalidArgumentException(sprintf('Destination class %s does not exist', $destClassName));
        }
        $fn = [];

        $fn[] = $this->fnBeforeMap($sourceMapAttr);
        $fn[] = $this->fnAfterMap($sourceMapAttr);
        $fn[] = $this->fnSourceMemberNamingConvention($sourceNamingConventionAttr);
        $fn[] = $this->fnConvertUsing($sourceMapAttr);

        $destReflectionClass = new \ReflectionClass($destClassName);
        $fn[] = $this->fnDestNamingConvention($destReflectionClass);
        $fn[] = $this->fnConstructUsing($destReflectionClass);

        // find all property attributes of the destination class that require a forMember configuration
        foreach ($this->propertyInfo->getProperties($destClassName) ?? [] as $property) {
            if (!$this->propertyInfo->isWritable($destClassName, $property)) {
                $this->logger?->debug(sprintf('Skipping non-writeable Property "%s::%s"', $destClassName, $property));
                continue;
            }

            $attributes = $this->attributeMetadataProvider->getPropertyAttributes($destClassName, $property, [
                Ignore::class,
                Condition::class,
                NullSubstitute::class,
                MapFrom::class,
            ]);

            if (0 === count($attributes)) {
                continue;
            }

            $fn[] = $this->fnForMember($property, $attributes, $sourceClassName);
        }

        return $this->newAnonymousProfile($sourceClassName, $destClassName, $fn);
    }

    public function fnNoop(): \Closure
    {
        return fn () => null;
    }

    private function fnBeforeMap(MapTo $sourceMapAttr): \Closure
    {
        $beforeMap = $sourceMapAttr->getBeforeMap();
        if (!$beforeMap) {
            return $this->fnNoop();
        }

        return function (Map $map) use ($beforeMap) {
            if (is_string($beforeMap)) {
                if ($this->container && $this->container->has($beforeMap)) {
                    $beforeMap = $this->container->get($beforeMap);
                } elseif (class_exists($beforeMap)) {
                    $beforeMap = new $beforeMap();
                } else {
                    throw new \InvalidArgumentException(sprintf('BeforeMap class "%s" does not exist', $beforeMap));
                }
            }

            Assert::isInstanceOf(
                $beforeMap,
                MappingActionInterface::class,
                sprintf(
                    'BeforeMap must be an instance of %s, got %s',
                    MappingActionInterface::class,
                    get_debug_type($beforeMap)
                )
            );

            $map->beforeMap($beforeMap);
        };
    }

    private function fnAfterMap(MapTo $sourceMapAttr): \Closure
    {
        $afterMap = $sourceMapAttr->getAfterMap();
        if (!$afterMap) {
            return $this->fnNoop();
        }

        return function (Map $map) use ($afterMap) {
            if (is_string($afterMap)) {
                if ($this->container && $this->container->has($afterMap)) {
                    $afterMap = $this->container->get($afterMap);
                } elseif (class_exists($afterMap)) {
                    $afterMap = new $afterMap();
                } else {
                    throw new \InvalidArgumentException(sprintf('AfterMap class "%s" does not exist', $afterMap));
                }
            }

            Assert::isInstanceOf(
                $afterMap,
                MappingActionInterface::class,
                sprintf(
                    'AfterMap must be an instance of %s, got %s',
                    MappingActionInterface::class,
                    get_debug_type($afterMap)
                )
            );

            $map->afterMap($afterMap);
        };
    }

    private function fnSourceMemberNamingConvention(?NamingConvention $sourceNamingConventionAttr): \Closure
    {
        if (!$sourceNamingConventionAttr) {
            return $this->fnNoop();
        }

        return function (Map $map) use ($sourceNamingConventionAttr) {
            $map->sourceMemberNamingConvention($sourceNamingConventionAttr->getNamingConvention());
        };
    }

    private function fnConvertUsing(MapTo $sourceMapAttr): \Closure
    {
        $convertUsing = $sourceMapAttr->getConvertUsing();
        if (!$convertUsing) {
            return $this->fnNoop();
        }

        return function (Map $map) use ($convertUsing) {
            if (is_string($convertUsing)) {
                if ($this->container && $this->container->has($convertUsing)) {
                    $convertUsing = $this->container->get($convertUsing);
                } elseif (class_exists($convertUsing)) {
                    $convertUsing = new $convertUsing();
                } else {
                    throw new \InvalidArgumentException(sprintf('ConvertUsing class "%s" does not exist', $convertUsing));
                }
            }

            Assert::isInstanceOf(
                $convertUsing,
                TypeConverterInterface::class,
                sprintf(
                    'ConvertUsing must be an instance of %s, got %s',
                    TypeConverterInterface::class,
                    get_debug_type($convertUsing)
                )
            );

            $map->convertUsing($convertUsing);
        };
    }

    /**
     * @param \ReflectionClass<object> $destReflectionClass
     */
    private function fnDestNamingConvention(\ReflectionClass $destReflectionClass): \Closure
    {
        $destNamingConventionAttr = $this->attributeMetadataProvider->getClassAttr(
            $destReflectionClass,
            NamingConvention::class
        );
        if (!$destNamingConventionAttr) {
            return $this->fnNoop();
        }

        return function (Map $map) use ($destNamingConventionAttr) {
            $map->destinationMemberNamingConvention($destNamingConventionAttr->getNamingConvention());
        };
    }

    /**
     * @param \ReflectionClass<object> $destReflectionClass
     */
    private function fnConstructUsing(\ReflectionClass $destReflectionClass): \Closure
    {
        $constructUsingAttr = $this->attributeMetadataProvider->getClassAttr(
            $destReflectionClass,
            ConstructUsing::class
        );
        if (!$constructUsingAttr) {
            return $this->fnNoop();
        }

        return fn (Map $map) => $map->constructUsing($constructUsingAttr->getConstructUsing());
    }

    /**
     * @param object[] $attributes
     *
     * @return \Closure
     */
    private function fnForMember(string $property, array $attributes, string $sourceClassName)
    {
        $expressionLanguage = $this->expressionLanguage;

        return function (Map $map) use ($property, $attributes, $sourceClassName, $expressionLanguage) {
            $map->forMember(
                $property,
                function (Options $options) use ($attributes, $expressionLanguage, $sourceClassName) {
                    foreach ($attributes as $attribute) {
                        if ($attribute instanceof Ignore) {
                            $options->ignore();
                        }

                        if ($attribute instanceof Condition && $attribute->getSource() === $sourceClassName) {
                            $options->condition(
                                fn (
                                    object $source,
                                    ResolutionContextInterface $context,
                                ) => $expressionLanguage->evaluate($attribute->getExpression(), [
                                    'source' => $source,
                                    'context' => $context,
                                ])
                            );
                        }

                        if ($attribute instanceof NullSubstitute) {
                            $options->nullSubstitute($attribute->getNullSubstitute());
                        }

                        if ($attribute instanceof MapFrom && $attribute->getSource() === $sourceClassName) {
                            $valueResolverOrExpression = $attribute->getValueResolverOrExpression();
                            if ($valueResolverOrExpression instanceof ValueResolverInterface) {
                                $options->mapFrom($valueResolverOrExpression);
                            }

                            if ($valueResolverOrExpression instanceof Expression) {
                                $options->mapFrom(
                                    fn (
                                        object $source,
                                        ResolutionContextInterface $context,
                                    ) => $expressionLanguage->evaluate($valueResolverOrExpression, [
                                        'source' => $source,
                                        'context' => $context,
                                    ])
                                );
                            }
                        }
                    }
                }
            );
        };
    }

    /**
     * @param callable[] $fn
     */
    protected function newAnonymousProfile(string $sourceClassName, string $destClassName, array $fn): AnonymousProfile
    {
        return new AnonymousProfile($sourceClassName, $destClassName, ...$fn);
    }
}
