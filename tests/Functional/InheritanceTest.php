<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Functional;

use Backbrain\Automapper\Contract\Builder\Config;
use Backbrain\Automapper\Contract\Builder\Options;
use Backbrain\Automapper\MapperConfiguration;
use Backbrain\Automapper\Tests\Fixtures\BaseLvl0Dest;
use Backbrain\Automapper\Tests\Fixtures\BaseLvl0Src;
use Backbrain\Automapper\Tests\Fixtures\BaseLvl1Dest;
use Backbrain\Automapper\Tests\Fixtures\BaseLvl1Src;
use Backbrain\Automapper\Tests\Fixtures\ScalarDest;
use Backbrain\Automapper\Tests\Fixtures\ScalarSrc;
use PHPUnit\Framework\TestCase;

class InheritanceTest extends TestCase
{
    public function testIncludeBaseMap(): void
    {
        $config = new MapperConfiguration(fn (Config $config) => $config
            ->createMap(ScalarSrc::class, ScalarDest::class)
        );

        $autoMapper = $config->createMapper();

        $source = new ScalarSrc('John Doe', 30, 1.75);
        $destination = $autoMapper->map($source, ScalarDest::class);
        $this->assertNull($destination->getBaseLvl1DestValue());

        $config = new MapperConfiguration(fn (Config $config) => $config
            ->createMap(BaseLvl1Src::class, BaseLvl1Dest::class)
                ->forMember('baseLvl1DestValue', fn (Options $opts) => $opts->mapFrom(fn (BaseLvl1Src $source) => $source->getBaseLvl1SrcValue()))
            ->createMap(ScalarSrc::class, ScalarDest::class)
                ->includeBase(BaseLvl1Src::class, BaseLvl1Dest::class)
        );

        $autoMapper = $config->createMapper();

        $source = new ScalarSrc('John Doe', 30, 1.75);
        $source->setBaseLvl1SrcValue('baseLvl1Value');
        $destination = $autoMapper->map($source, ScalarDest::class);
        $this->assertEquals('baseLvl1Value', $destination->getBaseLvl1DestValue());
    }

    public function testIncludeBaseMapMultiLevel(): void
    {
        $config = new MapperConfiguration(fn (Config $config) => $config
            ->createMap(BaseLvl0Src::class, BaseLvl0Dest::class)
                ->forMember('baseLvl0DestValue', fn (Options $opts) => $opts->mapFrom(fn (BaseLvl0Src $source) => $source->getBaseLvl0SrcValue()))
            ->createMap(BaseLvl1Src::class, BaseLvl1Dest::class)
                ->forMember('baseLvl1DestValue', fn (Options $opts) => $opts->mapFrom(fn (BaseLvl1Src $source) => $source->getBaseLvl1SrcValue()))
            ->createMap(ScalarSrc::class, ScalarDest::class)
                ->includeBase(BaseLvl1Src::class, BaseLvl1Dest::class)
        );

        $autoMapper = $config->createMapper();

        $source = new ScalarSrc('John Doe', 30, 1.75);
        $source->setBaseLvl1SrcValue('baseLvl1Value');
        $source->setBaseLvl0SrcValue('baseLvl0Value');
        $destination = $autoMapper->map($source, ScalarDest::class);
        $this->assertNull($destination->getBaseLvl0DestValue());

        $config = new MapperConfiguration(fn (Config $config) => $config
            ->createMap(BaseLvl0Src::class, BaseLvl0Dest::class)
                ->forMember('baseLvl0DestValue', fn (Options $opts) => $opts->mapFrom(fn (BaseLvl0Src $source) => $source->getBaseLvl0SrcValue()))
            ->createMap(BaseLvl1Src::class, BaseLvl1Dest::class)
                ->forMember('baseLvl1DestValue', fn (Options $opts) => $opts->mapFrom(fn (BaseLvl1Src $source) => $source->getBaseLvl1SrcValue()))
                ->includeBase(BaseLvl0Src::class, BaseLvl0Dest::class)
            ->createMap(ScalarSrc::class, ScalarDest::class)
                ->includeBase(BaseLvl1Src::class, BaseLvl1Dest::class)
        );

        $autoMapper = $config->createMapper();

        $source = new ScalarSrc('John Doe', 30, 1.75);
        $source->setBaseLvl1SrcValue('baseLvl1Value');
        $source->setBaseLvl0SrcValue('baseLvl0Value');
        $destination = $autoMapper->map($source, ScalarDest::class);

        $this->assertEquals('baseLvl1Value', $destination->getBaseLvl1DestValue());
        $this->assertEquals('baseLvl0Value', $destination->getBaseLvl0DestValue());
    }

    public function testInclude(): void
    {
        $config = new MapperConfiguration(fn (Config $config) => $config
//            ->createMap(BaseLvl0Src::class, BaseLvl0Dest::class)
//                ->forMember('baseLvl0DestValue', fn (MemberOptionsBuilderInterface $opts) => $opts->mapFrom(fn (BaseLvl0Src $source) => $source->getBaseLvl0SrcValue()))
//                ->include(BaseLvl1Src::class, BaseLvl1Dest::class)
            ->createMap(BaseLvl1Src::class, BaseLvl1Dest::class)
                ->forMember('baseLvl1DestValue', fn (Options $opts) => $opts->mapFrom(fn (BaseLvl1Src $source) => $source->getBaseLvl1SrcValue()))
                ->include(ScalarSrc::class, ScalarDest::class)
            ->createMap(ScalarSrc::class, ScalarDest::class)
        );

        $autoMapper = $config->createMapper();

        $source = new ScalarSrc('John Doe', 30, 1.75);
        $source->setBaseLvl1SrcValue('baseLvl1Value');
        $source->setBaseLvl0SrcValue('baseLvl0Value');
        $destination = $autoMapper->map($source, ScalarDest::class);

        $this->assertEquals('baseLvl1Value', $destination->getBaseLvl1DestValue());
        //        $this->assertEquals('baseLvl0Value', $destination->getBaseLvl0DestValue());
    }
}
