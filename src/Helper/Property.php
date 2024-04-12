<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Helper;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpStanExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoCacheExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;

class Property
{
    public static function newPropertyInfoExtractor(?CacheItemPoolInterface $cacheItemPool = null): PropertyInfoExtractorInterface
    {
        $phpStanExtractor = new PhpStanExtractor();
        $reflectionExtractor = new ReflectionExtractor();
        $phpDocExtractor = new PhpDocExtractor();

        $propertyInfoExtractor = new PropertyInfoExtractor(
            listExtractors: [$reflectionExtractor],
            typeExtractors: [$phpStanExtractor, $reflectionExtractor],
            descriptionExtractors: [$phpDocExtractor],
            accessExtractors: [$reflectionExtractor],
            initializableExtractors: [$reflectionExtractor]
        );

        return null === $cacheItemPool ? $propertyInfoExtractor : new PropertyInfoCacheExtractor($propertyInfoExtractor, $cacheItemPool);
    }

    public static function newPropertyAccessor(?CacheItemPoolInterface $cacheItemPool = null): PropertyAccessorInterface
    {
        return PropertyAccess::createPropertyAccessorBuilder()
            ->setCacheItemPool($cacheItemPool)
            ->getPropertyAccessor();
    }
}
