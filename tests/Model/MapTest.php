<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Model;

use Backbrain\Automapper\Contract\MapInterface;
use Backbrain\Automapper\Contract\MemberInterface;
use Backbrain\Automapper\Contract\NamingConventionInterface;
use Backbrain\Automapper\Contract\TypeConverterInterface;
use Backbrain\Automapper\Contract\TypeFactoryInterface;
use Backbrain\Automapper\Model\Map;
use PHPUnit\Framework\TestCase;

class MapTest extends TestCase
{
    public function testMapCanBeCreatedFromAnotherMap()
    {
        $sourceMap = $this->createMock(MapInterface::class);
        $sourceMap->method('getSourceType')->willReturn('sourceType');
        $sourceMap->method('getDestinationType')->willReturn('destinationType');
        $sourceMap->method('getMembers')->willReturn(['members']);
        $sourceMap->method('getAs')->willReturn('mappedBy');
        $sourceMap->method('getTypeConverter')->willReturn($this->createMock(TypeConverterInterface::class));
        $sourceMap->method('getTypeFactory')->willReturn($this->createMock(TypeFactoryInterface::class));
        $sourceMap->method('getSourceMemberNamingConvention')->willReturn($this->createMock(NamingConventionInterface::class));
        $sourceMap->method('getDestinationMemberNamingConvention')->willReturn($this->createMock(NamingConventionInterface::class));

        $newMap = Map::from($sourceMap);

        $this->assertInstanceOf(Map::class, $newMap);
        $this->assertEquals('sourceType', $newMap->getSourceType());
        $this->assertEquals('destinationType', $newMap->getDestinationType());
        $this->assertEquals(['members'], $newMap->getMembers());
        $this->assertEquals('mappedBy', $newMap->getAs());
        $this->assertInstanceOf(TypeConverterInterface::class, $newMap->getTypeConverter());
        $this->assertInstanceOf(TypeFactoryInterface::class, $newMap->getTypeFactory());
        $this->assertInstanceOf(NamingConventionInterface::class, $newMap->getSourceMemberNamingConvention());
        $this->assertInstanceOf(NamingConventionInterface::class, $newMap->getDestinationMemberNamingConvention());
    }

    public function testMapCanBeModifiedWithMembers()
    {
        $map = Map::from($this->createMock(MapInterface::class));
        $members = $this->createMock(MemberInterface::class);
        $newMap = $map->withMembers($members);

        $this->assertInstanceOf(Map::class, $newMap);
        $this->assertEquals([$members], $newMap->getMembers());
    }

    public function testMapCanBeModifiedWithTypeConverter()
    {
        $map = Map::from($this->createMock(MapInterface::class));
        $typeConverter = $this->createMock(TypeConverterInterface::class);
        $newMap = $map->withTypeConverter($typeConverter);

        $this->assertInstanceOf(Map::class, $newMap);
        $this->assertEquals($typeConverter, $newMap->getTypeConverter());
    }

    public function testMapCanBeModifiedWithTypeFactory()
    {
        $map = Map::from($this->createMock(MapInterface::class));
        $typeFactory = $this->createMock(TypeFactoryInterface::class);
        $newMap = $map->withTypeFactory($typeFactory);

        $this->assertInstanceOf(Map::class, $newMap);
        $this->assertEquals($typeFactory, $newMap->getTypeFactory());
    }

    public function testMapCanBeModifiedWithMappedBy()
    {
        $map = Map::from($this->createMock(MapInterface::class));
        $newMap = $map->withMappedBy('mappedBy');

        $this->assertInstanceOf(Map::class, $newMap);
        $this->assertEquals('mappedBy', $newMap->getAs());
    }

    public function testMapCanBeModifiedWithSourceMemberNamingConvention()
    {
        $map = Map::from($this->createMock(MapInterface::class));
        $namingConvention = $this->createMock(NamingConventionInterface::class);
        $newMap = $map->withSourceMemberNamingConvention($namingConvention);

        $this->assertInstanceOf(Map::class, $newMap);
        $this->assertEquals($namingConvention, $newMap->getSourceMemberNamingConvention());
    }

    public function testMapCanBeModifiedWithDestinationMemberNamingConvention()
    {
        $map = Map::from($this->createMock(MapInterface::class));
        $namingConvention = $this->createMock(NamingConventionInterface::class);
        $newMap = $map->withDestinationMemberNamingConvention($namingConvention);

        $this->assertInstanceOf(Map::class, $newMap);
        $this->assertEquals($namingConvention, $newMap->getDestinationMemberNamingConvention());
    }

    public function testMapCanBeModifiedWithDestinationType()
    {
        $map = Map::from($this->createMock(MapInterface::class));
        $newMap = $map->withDestinationType('destinationType');

        $this->assertInstanceOf(Map::class, $newMap);
        $this->assertEquals('destinationType', $newMap->getDestinationType());
    }

    public function testMapCanBeModifiedWithSourceType()
    {
        $map = Map::from($this->createMock(MapInterface::class));
        $newMap = $map->withSourceType('sourceType');

        $this->assertInstanceOf(Map::class, $newMap);
        $this->assertEquals('sourceType', $newMap->getSourceType());
    }
}
