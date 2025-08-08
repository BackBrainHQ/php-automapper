<?php

declare(strict_types=1);

namespace Backbrain\Automapper;

use Backbrain\Automapper\Context\ResolutionContextProvider;
use Backbrain\Automapper\Contract\AutoMapperInterface;
use Backbrain\Automapper\Contract\Builder\Config;
use Backbrain\Automapper\Contract\Metadata\AttributeMetadataProviderInterface;
use Backbrain\Automapper\Contract\Metadata\ClassMetadataProviderInterface;
use Backbrain\Automapper\Contract\ProfileInterface;
use Backbrain\Automapper\Contract\ResolutionContextProviderInterface;
use Backbrain\Automapper\Helper\Property;
use Backbrain\Automapper\Metadata\AttributeMetadataProvider;
use Backbrain\Automapper\Metadata\ClassMetadataProvider;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;

final class Factory implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private CacheItemPoolInterface $cacheItemPool;

    private ExpressionLanguage $expressionLanguage;

    private ClassMetadataProviderInterface $classMetadataProvider;

    private AttributeMetadataProviderInterface $attributeMetadataProvider;

    private PropertyInfoExtractorInterface $propertyInfo;

    private ResolutionContextProviderInterface $resolutionContextProvider;

    /**
     * @var class-string[]
     */
    private array $classes = [];

    /**
     * @var ProfileInterface[]
     */
    private array $profiles = [];

    public function __construct(
        ?LoggerInterface $logger = null,
        ?CacheItemPoolInterface $cacheItemPool = null,
        ?ExpressionLanguage $expressionLanguage = null,
        ?PropertyInfoExtractorInterface $propertyInfo = null,
        ?ClassMetadataProviderInterface $classMetadataProvider = null,
        ?AttributeMetadataProviderInterface $attributeMetadataProvider = null,
        ?ResolutionContextProviderInterface $resolutionContextProvider = null,
        ?ContainerInterface $container = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
        $this->cacheItemPool = $cacheItemPool ?? new ArrayAdapter();
        $this->resolutionContextProvider = $resolutionContextProvider ?? new ResolutionContextProvider();
        $this->expressionLanguage = $expressionLanguage ?? new ExpressionLanguage($this->cacheItemPool);
        $this->propertyInfo = $propertyInfo ?? Property::newPropertyInfoExtractor($this->cacheItemPool);
        $this->attributeMetadataProvider = $attributeMetadataProvider ?? new AttributeMetadataProvider();
        $this->classMetadataProvider = $classMetadataProvider ?? new ClassMetadataProvider(
            logger: $this->logger,
            cacheItemPool: $this->cacheItemPool,
            expressionLanguage: $this->expressionLanguage,
            propertyInfo: $this->propertyInfo,
            attributeMetadataProvider: $this->attributeMetadataProvider,
            container: $container,
        );
    }

    public function create(): AutoMapperInterface
    {
        $mapperConfig = new MapperConfiguration(function (Config $config) {
            foreach ($this->profiles as $profile) {
                $config->addProfile($profile);
            }

            foreach ($this->classes as $class) {
                foreach ($this->classMetadataProvider->getProfiles($class) as $profile) {
                    $config->addProfile($profile);
                }
            }
        });

        return new AutoMapper(
            mapperConfiguration: $mapperConfig,
            cacheItemPool: $this->cacheItemPool,
            logger: $this->logger,
            resolutionContextProvider: $this->resolutionContextProvider,
        );
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
}
