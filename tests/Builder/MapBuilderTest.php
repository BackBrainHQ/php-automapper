<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Builder;

use Backbrain\Automapper\Builder\DefaultMap;
use Backbrain\Automapper\Context\ResolutionContext;
use Backbrain\Automapper\Contract\Builder\Config;
use Backbrain\Automapper\Contract\Builder\Map;
use Backbrain\Automapper\Contract\Builder\Options;
use Backbrain\Automapper\Contract\MapInterface;
use Backbrain\Automapper\Contract\NamingConventionInterface;
use Backbrain\Automapper\Contract\ResolutionContextInterface;
use Backbrain\Automapper\Contract\TypeConverterInterface;
use Backbrain\Automapper\Profiles\ScalarToStringProfile;
use PHPUnit\Framework\TestCase;

class MapBuilderTest extends TestCase
{
    public function testMapBuilderShouldCreateMap()
    {
        $mapBuilder = new DefaultMap($this->createMock(Config::class), 'SourceClass', 'DestinationClass');

        $this->assertInstanceOf(Map::class, $mapBuilder->createMap('AnotherSourceClass', 'AnotherDestinationClass'));
    }

    public function testMapBuilderShouldAddProfile()
    {
        $mapBuilder = new DefaultMap($this->createMock(Config::class), 'SourceClass', 'DestinationClass');

        $this->assertInstanceOf(Config::class, $mapBuilder->addProfile(new ScalarToStringProfile()));
    }

    public function testMapBuilderShouldHandleMemberOptions()
    {
        $mapBuilder = new DefaultMap($this->createMock(Config::class), 'SourceClass', 'DestinationClass');

        $this->assertInstanceOf(DefaultMap::class, $mapBuilder->forMember('destinationProperty', function () {}));
    }

    public function testMapBuilderShouldHandleTypeConverter()
    {
        $mapBuilder = new DefaultMap($this->createMock(Config::class), 'SourceClass', 'DestinationClass');

        $this->assertInstanceOf(DefaultMap::class, $mapBuilder->convertUsing($this->createMock(TypeConverterInterface::class)));
    }

    public function testMapBuilderShouldHandleCallableTypeConverter()
    {
        $mapBuilder = new DefaultMap($this->createMock(Config::class), 'SourceClass', 'DestinationClass');

        $this->assertInstanceOf(DefaultMap::class, $mapBuilder->convertUsing(function ($source, ResolutionContextInterface $context) {
            return $source;
        }));
    }

    public function testMapBuilderShouldHandleSourceMemberNamingConvention()
    {
        $mapBuilder = new DefaultMap($this->createMock(Config::class), 'SourceClass', 'DestinationClass');

        $this->assertInstanceOf(DefaultMap::class, $mapBuilder->sourceMemberNamingConvention($this->createMock(NamingConventionInterface::class)));
    }

    public function testMapBuilderShouldHandleDestinationMemberNamingConvention()
    {
        $mapBuilder = new DefaultMap($this->createMock(Config::class), 'SourceClass', 'DestinationClass');

        $this->assertInstanceOf(DefaultMap::class, $mapBuilder->destinationMemberNamingConvention($this->createMock(NamingConventionInterface::class)));
    }

    public function testMapBuilderShouldBuildMap()
    {
        $mapBuilder = new DefaultMap($this->createMock(Config::class), 'SourceClass', 'DestinationClass');

        $this->assertInstanceOf(MapInterface::class, $mapBuilder->build());
    }

    public function testMapBuilderShouldDeduplicateMembers()
    {
        $mapBuilder = new DefaultMap($this->createMock(Config::class), 'SourceClass', 'DestinationClass');

        $mapBuilder->forMember('destinationProperty', fn (Options $builder) => $builder->mapFrom(fn () => 'first'));
        $mapBuilder->forMember('destinationProperty', fn (Options $builder) => $builder->mapFrom(fn () => 'last'));

        $map = $mapBuilder->build();

        $map->getMembers();
        $this->assertCount(1, $map->getMembers());
        $this->assertEquals('last', $map->getMembers()[0]->getValueProvider()->resolve(new \stdClass(), new ResolutionContext()));
    }

    public function testInvalidSourceMemberNamingConventionShouldThrowException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $mapBuilder = new DefaultMap($this->createMock(Config::class), 'SourceClass', 'DestinationClass');
        $mapBuilder->sourceMemberNamingConvention('InvalidNamingConvention');
    }

    public function testInvalidDestinationMemberNamingConventionShouldThrowException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $mapBuilder = new DefaultMap($this->createMock(Config::class), 'SourceClass', 'DestinationClass');
        $mapBuilder->destinationMemberNamingConvention('InvalidNamingConvention');
    }

    public function testSourceMemberNamingConventionShouldThrowExceptionForNonExistentClass()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/does not exist/');

        $mapBuilder = new DefaultMap($this->createMock(Config::class), 'SourceClass', 'DestinationClass');
        $mapBuilder->sourceMemberNamingConvention('NonExistentClass');
    }

    public function testDestinationMemberNamingConventionShouldThrowExceptionForNonExistentClass()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/does not exist/');

        $mapBuilder = new DefaultMap($this->createMock(Config::class), 'SourceClass', 'DestinationClass');
        $mapBuilder->destinationMemberNamingConvention('NonExistentClass');
    }

    public function testSourceMemberNamingConventionShouldAcceptValidClass()
    {
        $mapBuilder = new DefaultMap($this->createMock(Config::class), 'SourceClass', 'DestinationClass');
        $namingConvention = $this->createMock(NamingConventionInterface::class);

        $this->assertInstanceOf(DefaultMap::class, $mapBuilder->sourceMemberNamingConvention($namingConvention));
    }

    public function testDestinationMemberNamingConventionShouldAcceptValidClass()
    {
        $mapBuilder = new DefaultMap($this->createMock(Config::class), 'SourceClass', 'DestinationClass');
        $namingConvention = $this->createMock(NamingConventionInterface::class);

        $this->assertInstanceOf(DefaultMap::class, $mapBuilder->destinationMemberNamingConvention($namingConvention));
    }

    public function testConvertUsingShouldAcceptTypeConverterInterface()
    {
        $mapBuilder = new DefaultMap($this->createMock(Config::class), 'SourceClass', 'DestinationClass');
        $typeConverter = $this->createMock(TypeConverterInterface::class);

        $this->assertInstanceOf(DefaultMap::class, $mapBuilder->convertUsing($typeConverter));
    }

    public function testConvertUsingShouldAcceptCallable()
    {
        $mapBuilder = new DefaultMap($this->createMock(Config::class), 'SourceClass', 'DestinationClass');
        $callable = function () { return 'value'; };

        $this->assertInstanceOf(DefaultMap::class, $mapBuilder->convertUsing($callable));
    }
}
