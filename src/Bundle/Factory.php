<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Bundle;

use Backbrain\Automapper\AutoMapper;
use Backbrain\Automapper\Contract\Attributes\FromExpr;
use Backbrain\Automapper\Contract\Attributes\FromFn;
use Backbrain\Automapper\Contract\AutoMapperInterface;
use Backbrain\Automapper\Contract\Builder\Config;
use Backbrain\Automapper\Contract\Builder\Map;
use Backbrain\Automapper\Contract\Builder\Options;
use Backbrain\Automapper\Contract\ProfileInterface;
use Backbrain\Automapper\Helper\Property;
use Backbrain\Automapper\MapperConfiguration;
use Backbrain\Automapper\Profile;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;

final class Factory implements LoggerAwareInterface
{
    private ?LoggerInterface $logger = null;

    private ?CacheItemPoolInterface $cacheItemPool = null;

    /**
     * @var array<array<string, mixed>>
     */
    private array $models = [];

    /**
     * @var ProfileInterface[]
     */
    private array $profiles = [];

    public function create(): AutoMapperInterface
    {
        $mapperConfig = new MapperConfiguration(function (Config $config) {
            foreach ($this->profiles as $profile) {
                $config->addProfile($profile);
            }

            $propertyInfo = Property::newPropertyInfoExtractor($this->cacheItemPool);
            foreach ($this->models as $mappingOptions) {
                $profile = $this->newProfile($propertyInfo, $mappingOptions);
                $config->addProfile($profile);
            }
        });

        return new AutoMapper($mapperConfig, $this->cacheItemPool, $this->logger);
    }

    /**
     * @param array<string, mixed> $mappingOptions
     */
    private function newProfile(PropertyInfoExtractorInterface $propertyInfo, array $mappingOptions): ProfileInterface
    {
        return new class($this->profiles, $propertyInfo, $mappingOptions, $this->cacheItemPool) extends Profile {
            /**
             * @param ProfileInterface[]   $profiles
             * @param array<string, mixed> $mappingOptions
             */
            public function __construct(
                private array $profiles,
                PropertyInfoExtractorInterface $propertyInfo,
                array $mappingOptions,
                private ?CacheItemPoolInterface $cacheItemPool = null
            ) {
                $optSourceType = $mappingOptions['source'] ?? null;
                assert(is_string($optSourceType), 'Source must be a string');

                $optDestinationType = $mappingOptions['dest'] ?? null;
                assert(is_string($optDestinationType), 'Destination must be a string');

                $optInclude = $mappingOptions['include'] ?? [];
                assert(is_array($optInclude), 'Include must be an array');

                $map = $this->createMap($optSourceType, $optDestinationType);
                foreach ($optInclude as $include) {
                    $map->include($include, $optDestinationType);
                }

                foreach ($propertyInfo->getProperties($optDestinationType) ?? [] as $property) {
                    $attributes = $this->getAttributes($optDestinationType, $property);
                    foreach ($attributes as $attribute) {
                        switch ($attribute->getName()) {
                            case FromFn::class:
                                $this->newFromFn($attribute, $map, $property);
                                break;
                            case FromExpr::class:
                                $this->newFromExpr($attribute, $map, $property);
                                break;
                        }
                    }
                }
            }

            /**
             * @return \ReflectionAttribute<object>[]
             */
            private function getAttributes(string $class, string $property): array
            {
                $reflection = new \ReflectionProperty($class, $property);

                return $reflection->getAttributes();
            }

            /**
             * @param \ReflectionAttribute<object> $attribute
             */
            private function newFromFn(\ReflectionAttribute $attribute, Map $map, string $property): void
            {
                $args = $attribute->getArguments();
                $fn = $args['func'];
                foreach ($this->profiles as $profile) {
                    if (get_class($profile) === $fn[0]) {
                        $fn[0] = $profile;
                        break;
                    }
                }

                $map->forMember($property, fn (Options $options) => $options->mapFrom($fn));
            }

            /**
             * @param \ReflectionAttribute<object> $attribute
             */
            private function newFromExpr(\ReflectionAttribute $attribute, Map $map, string $property): void
            {
                $expressionLanguage = new ExpressionLanguage($this->cacheItemPool);

                $args = $attribute->getArguments();
                $expr = $args['expr'];
                $map->forMember($property, fn (Options $options) => $options->mapFrom(fn ($source, $ctx) => $expressionLanguage->evaluate($expr, [
                    'source' => $source,
                    'ctx' => $ctx,
                ])));
            }
        };
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function setCacheItemPool(?CacheItemPoolInterface $cacheItemPool): void
    {
        $this->cacheItemPool = $cacheItemPool;
    }

    public function addProfile(ProfileInterface $profile): void
    {
        $this->profiles[] = $profile;
    }

    /**
     * @param string[] $include
     */
    public function addModel(string $source, string $dest, array $include = []): void
    {
        $this->models[] = [
            'source' => $source,
            'dest' => $dest,
            'include' => $include,
        ];
    }
}
