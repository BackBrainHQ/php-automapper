<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Builder;

use Backbrain\Automapper\Builder\MapBuilder;
use Backbrain\Automapper\Contract\Builder\MapBuilderInterface;
use Backbrain\Automapper\Contract\Builder\MemberOptionsBuilderInterface;
use Backbrain\Automapper\Contract\Builder\ProfileBuilderInterface;
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
        $mapBuilder = new MapBuilder($this->createMock(ProfileBuilderInterface::class), 'SourceClass', 'DestinationClass');

        $this->assertInstanceOf(MapBuilderInterface::class, $mapBuilder->createMap('AnotherSourceClass', 'AnotherDestinationClass'));
    }

    public function testMapBuilderShouldAddProfile()
    {
        $mapBuilder = new MapBuilder($this->createMock(ProfileBuilderInterface::class), 'SourceClass', 'DestinationClass');

        $this->assertInstanceOf(ProfileBuilderInterface::class, $mapBuilder->addProfile(new ScalarToStringProfile()));
    }

    public function testMapBuilderShouldHandleMemberOptions()
    {
        $mapBuilder = new MapBuilder($this->createMock(ProfileBuilderInterface::class), 'SourceClass', 'DestinationClass');

        $this->assertInstanceOf(MapBuilder::class, $mapBuilder->forMember('destinationProperty', function () {}));
    }

    public function testMapBuilderShouldHandleTypeConverter()
    {
        $mapBuilder = new MapBuilder($this->createMock(ProfileBuilderInterface::class), 'SourceClass', 'DestinationClass');

        $this->assertInstanceOf(MapBuilder::class, $mapBuilder->convertUsing($this->createMock(TypeConverterInterface::class)));
    }

    public function testMapBuilderShouldHandleCallableTypeConverter()
    {
        $mapBuilder = new MapBuilder($this->createMock(ProfileBuilderInterface::class), 'SourceClass', 'DestinationClass');

        $this->assertInstanceOf(MapBuilder::class, $mapBuilder->convertUsing(function ($source, ResolutionContextInterface $context) {
            return $source;
        }));
    }

    public function testMapBuilderShouldHandleSourceMemberNamingConvention()
    {
        $mapBuilder = new MapBuilder($this->createMock(ProfileBuilderInterface::class), 'SourceClass', 'DestinationClass');

        $this->assertInstanceOf(MapBuilder::class, $mapBuilder->sourceMemberNamingConvention($this->createMock(NamingConventionInterface::class)));
    }

    public function testMapBuilderShouldHandleDestinationMemberNamingConvention()
    {
        $mapBuilder = new MapBuilder($this->createMock(ProfileBuilderInterface::class), 'SourceClass', 'DestinationClass');

        $this->assertInstanceOf(MapBuilder::class, $mapBuilder->destinationMemberNamingConvention($this->createMock(NamingConventionInterface::class)));
    }

    public function testMapBuilderShouldBuildMap()
    {
        $mapBuilder = new MapBuilder($this->createMock(ProfileBuilderInterface::class), 'SourceClass', 'DestinationClass');

        $this->assertInstanceOf(MapInterface::class, $mapBuilder->build());
    }

    public function testMapBuilderShouldDeduplicateMembers()
    {
        $mapBuilder = new MapBuilder($this->createMock(ProfileBuilderInterface::class), 'SourceClass', 'DestinationClass');

        $mapBuilder->forMember('destinationProperty', fn (MemberOptionsBuilderInterface $builder) => $builder->mapFrom(fn () => 'first'));
        $mapBuilder->forMember('destinationProperty', fn (MemberOptionsBuilderInterface $builder) => $builder->mapFrom(fn () => 'last'));

        $map = $mapBuilder->build();

        $map->getMembers();
        $this->assertCount(1, $map->getMembers());
        $this->assertEquals('last', $map->getMembers()[0]->getValueProvider()->resolve(new \stdClass()));
    }

    public function testInvalidSourceMemberNamingConventionShouldThrowException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $mapBuilder = new MapBuilder($this->createMock(ProfileBuilderInterface::class), 'SourceClass', 'DestinationClass');
        $mapBuilder->sourceMemberNamingConvention('InvalidNamingConvention');
    }

    public function testInvalidDestinationMemberNamingConventionShouldThrowException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $mapBuilder = new MapBuilder($this->createMock(ProfileBuilderInterface::class), 'SourceClass', 'DestinationClass');
        $mapBuilder->destinationMemberNamingConvention('InvalidNamingConvention');
    }

    public function testSourceMemberNamingConventionShouldThrowExceptionForNonExistentClass()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/does not exist/');

        $mapBuilder = new MapBuilder($this->createMock(ProfileBuilderInterface::class), 'SourceClass', 'DestinationClass');
        $mapBuilder->sourceMemberNamingConvention('NonExistentClass');
    }

    public function testDestinationMemberNamingConventionShouldThrowExceptionForNonExistentClass()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/does not exist/');

        $mapBuilder = new MapBuilder($this->createMock(ProfileBuilderInterface::class), 'SourceClass', 'DestinationClass');
        $mapBuilder->destinationMemberNamingConvention('NonExistentClass');
    }

    public function testSourceMemberNamingConventionShouldAcceptValidClass()
    {
        $mapBuilder = new MapBuilder($this->createMock(ProfileBuilderInterface::class), 'SourceClass', 'DestinationClass');
        $namingConvention = $this->createMock(NamingConventionInterface::class);

        $this->assertInstanceOf(MapBuilder::class, $mapBuilder->sourceMemberNamingConvention($namingConvention));
    }

    public function testDestinationMemberNamingConventionShouldAcceptValidClass()
    {
        $mapBuilder = new MapBuilder($this->createMock(ProfileBuilderInterface::class), 'SourceClass', 'DestinationClass');
        $namingConvention = $this->createMock(NamingConventionInterface::class);

        $this->assertInstanceOf(MapBuilder::class, $mapBuilder->destinationMemberNamingConvention($namingConvention));
    }

    public function testConvertUsingShouldAcceptTypeConverterInterface()
    {
        $mapBuilder = new MapBuilder($this->createMock(ProfileBuilderInterface::class), 'SourceClass', 'DestinationClass');
        $typeConverter = $this->createMock(TypeConverterInterface::class);

        $this->assertInstanceOf(MapBuilder::class, $mapBuilder->convertUsing($typeConverter));
    }

    public function testConvertUsingShouldAcceptCallable()
    {
        $mapBuilder = new MapBuilder($this->createMock(ProfileBuilderInterface::class), 'SourceClass', 'DestinationClass');
        $callable = function () { return 'value'; };

        $this->assertInstanceOf(MapBuilder::class, $mapBuilder->convertUsing($callable));
    }
}
