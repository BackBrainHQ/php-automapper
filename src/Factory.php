<?php

declare(strict_types=1);

namespace Backbrain\Automapper;

use Backbrain\Automapper\Contract\Attributes\Condition;
use Backbrain\Automapper\Contract\Attributes\ConstructUsing;
use Backbrain\Automapper\Contract\Attributes\Ignore;
use Backbrain\Automapper\Contract\Attributes\MapFrom;
use Backbrain\Automapper\Contract\Attributes\MapTo;
use Backbrain\Automapper\Contract\Attributes\NamingConvention;
use Backbrain\Automapper\Contract\Attributes\NullSubstitute;
use Backbrain\Automapper\Contract\AutoMapperInterface;
use Backbrain\Automapper\Contract\Builder\Config;
use Backbrain\Automapper\Contract\Builder\Options;
use Backbrain\Automapper\Contract\MappingActionInterface;
use Backbrain\Automapper\Contract\NamingConventionInterface;
use Backbrain\Automapper\Contract\ProfileInterface;
use Backbrain\Automapper\Contract\ResolutionContextInterface;
use Backbrain\Automapper\Contract\TypeConverterInterface;
use Backbrain\Automapper\Contract\TypeFactoryInterface;
use Backbrain\Automapper\Helper\Attribute;
use Backbrain\Automapper\Helper\Property;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;

final class Factory implements LoggerAwareInterface
{
    private LoggerInterface $logger;

    private CacheItemPoolInterface $cacheItemPool;

    private ExpressionLanguage $expressionLanguage;

    /**
     * @var class-string[]
     */
    private array $classes = [];

    /**
     * @var ProfileInterface[]
     */
    private array $profiles = [];

    public function __construct(?LoggerInterface $logger = null, ?CacheItemPoolInterface $cacheItemPool = null, ?ExpressionLanguage $expressionLanguage = null)
    {
        $this->logger = $logger ?? new NullLogger();
        $this->cacheItemPool = $cacheItemPool ?? new ArrayAdapter();
        $this->expressionLanguage = $expressionLanguage ?? new ExpressionLanguage($this->cacheItemPool);
    }

    public function create(): AutoMapperInterface
    {
        $mapperConfig = new MapperConfiguration(function (Config $config) {
            foreach ($this->profiles as $profile) {
                $config->addProfile($profile);
            }

            foreach ($this->classes as $class) {
                foreach ($this->buildProfilesFromClassAttributes($class) as $profile) {
                    $config->addProfile($profile);
                }
            }
        });

        return new AutoMapper($mapperConfig, $this->cacheItemPool, $this->logger);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function addProfile(ProfileInterface $profile): void
    {
        $this->profiles[] = $profile;
    }

    /**
     * @param class-string $className
     */
    public function addClass(string $className): void
    {
        if (!class_exists($className)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist', $className));
        }

        $this->classes[] = $className;
    }

    /**
     * @param class-string $className
     *
     * @return ProfileInterface[]
     */
    private function buildProfilesFromClassAttributes(string $className): array
    {
        if (!class_exists($className)) {
            throw new \InvalidArgumentException(sprintf('Class %s does not exist', $className));
        }

        $propertyInfo = Property::newPropertyInfoExtractor($this->cacheItemPool);
        $sourceReflectionClass = new \ReflectionClass($className);

        $profiles = [];

        $mapAttrs = Attribute::getClassAttrs($sourceReflectionClass, MapTo::class);
        $sourceNamingConventionAttr = Attribute::getClassAttr($sourceReflectionClass, NamingConvention::class);

        foreach ($mapAttrs as $mapAttr) {
            $profile = $this->profileFromClass($propertyInfo, $className, $mapAttr, $sourceNamingConventionAttr);

            $profiles[] = $profile;
        }

        return $profiles;
    }

    public function profileFromClass(PropertyInfoExtractorInterface $propertyInfo, string $sourceClassName, MapTo $sourceMapAttr, ?NamingConvention $sourceNamingConventionAttr): ProfileInterface
    {
        return new class(propertyInfo: $propertyInfo, expressionLanguage: $this->expressionLanguage, sourceClassName: $sourceClassName, sourceMapAttr: $sourceMapAttr, sourceNamingConventionAttr: $sourceNamingConventionAttr) extends Profile {
            public function __construct(
                PropertyInfoExtractorInterface $propertyInfo,
                string $sourceClassName,
                MapTo $sourceMapAttr,
                ExpressionLanguage $expressionLanguage,
                ?NamingConvention $sourceNamingConventionAttr = null
            ) {
                $destClassName = $sourceMapAttr->getDest();
                if (!class_exists($destClassName)) {
                    throw new \InvalidArgumentException(sprintf('Class %s does not exist', $destClassName));
                }

                $map = $this->createMap($sourceClassName, $destClassName);

                $beforeMap = $sourceMapAttr->getBeforeMap();
                if ($beforeMap) {
                    $beforeMap = $beforeMap instanceof MappingActionInterface
                        ? $beforeMap
                        : new ($beforeMap);

                    if (!$beforeMap instanceof MappingActionInterface) {
                        throw new \InvalidArgumentException('BeforeMap must be an instance of MappingActionInterface');
                    }

                    $map->beforeMap($beforeMap);
                }

                $afterMap = $sourceMapAttr->getAfterMap();
                if ($afterMap) {
                    $afterMap = $afterMap instanceof MappingActionInterface
                        ? $afterMap
                        : new ($afterMap);

                    if (!$afterMap instanceof MappingActionInterface) {
                        throw new \InvalidArgumentException('AfterMap must be an instance of MappingActionInterface');
                    }

                    $map->afterMap($afterMap);
                }

                if ($sourceNamingConventionAttr instanceof NamingConvention) {
                    $namingConvention = $sourceNamingConventionAttr->getNamingConvention() instanceof NamingConventionInterface
                        ? $sourceNamingConventionAttr->getNamingConvention()
                        : new ($sourceNamingConventionAttr->getNamingConvention());

                    if (!$namingConvention instanceof NamingConventionInterface) {
                        throw new \InvalidArgumentException('NamingConvention must be an instance of NamingConventionInterface');
                    }

                    $map->sourceMemberNamingConvention($namingConvention);
                }

                $convertUsing = $sourceMapAttr->getConvertUsing();
                if ($convertUsing) {
                    $convertUsing = $convertUsing instanceof TypeConverterInterface
                        ? $convertUsing
                        : new ($convertUsing);

                    if (!$convertUsing instanceof TypeConverterInterface) {
                        throw new \InvalidArgumentException('ConvertUsing must be an instance of TypeConverterInterface');
                    }

                    $map->convertUsing($convertUsing);
                }

                // look at the reverse side to collect the members
                $destReflectionClass = new \ReflectionClass($destClassName);

                $destNamingConventionAttr = Attribute::getClassAttr($destReflectionClass, NamingConvention::class);
                if ($destNamingConventionAttr) {
                    $destNamingConvention = $destNamingConventionAttr->getNamingConvention() instanceof NamingConventionInterface
                        ? $destNamingConventionAttr->getNamingConvention()
                        : new ($destNamingConventionAttr->getNamingConvention());

                    if (!$destNamingConvention instanceof NamingConventionInterface) {
                        throw new \InvalidArgumentException('NamingConvention must be an instance of NamingConventionInterface');
                    }

                    $map->destinationMemberNamingConvention($destNamingConvention);
                }

                $constructUsingAttr = Attribute::getClassAttr($destReflectionClass, ConstructUsing::class);
                if ($constructUsingAttr) {
                    $constructUsing = $constructUsingAttr->getConstructUsing() instanceof TypeFactoryInterface
                        ? $constructUsingAttr->getConstructUsing()
                        : new ($constructUsingAttr->getConstructUsing());

                    if (!$constructUsing instanceof TypeFactoryInterface) {
                        throw new \InvalidArgumentException('ConstructUsing must be an instance of TypeFactoryInterface');
                    }

                    $map->constructUsing($constructUsing);
                }

                foreach ($propertyInfo->getProperties($destClassName) ?? [] as $property) {
                    if (!$propertyInfo->isWritable($destClassName, $property)) {
                        continue;
                    }

                    $attributes = Attribute::getPropertyAttributes($destClassName, $property, [Ignore::class, Condition::class, NullSubstitute::class, MapFrom::class]);
                    if (0 === count($attributes)) {
                        continue;
                    }

                    $map->forMember($property, function (Options $options) use ($attributes, $expressionLanguage, $sourceClassName) {
                        foreach ($attributes as $attribute) {
                            if ($attribute instanceof Ignore) {
                                $options->ignore();
                            }

                            if ($attribute instanceof Condition) {
                                $options->condition(fn (object $source, ResolutionContextInterface $context) => $expressionLanguage->evaluate($attribute->getCondition(), [
                                    'source' => $source,
                                    'context' => $context,
                                ]));
                            }

                            if ($attribute instanceof NullSubstitute) {
                                $options->nullSubstitute($attribute->getNullSubstitute());
                            }

                            if ($attribute instanceof MapFrom && $attribute->getSource() === $sourceClassName) {
                                $mapFrom = $attribute->getMapFrom();
                                if (is_string($mapFrom)) {
                                    $mapFrom = fn (object $source, ResolutionContextInterface $context) => $expressionLanguage->evaluate($mapFrom, [
                                        'source' => $source,
                                        'context' => $context,
                                    ]);
                                }

                                $options->mapFrom($mapFrom);
                            }
                        }
                    });
                }
            }
        };
    }
}
