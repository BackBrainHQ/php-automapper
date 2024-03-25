<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Functional;

use Backbrain\Automapper\AutoMapper;
use Backbrain\Automapper\Contract\Builder\Config;
use Backbrain\Automapper\Contract\Builder\Options;
use Backbrain\Automapper\Converter\Naming\CamelCaseNamingConvention;
use Backbrain\Automapper\Converter\Naming\SnakeCaseNamingConvention;
use Backbrain\Automapper\Exceptions\MapperException;
use Backbrain\Automapper\MapperConfiguration;
use Backbrain\Automapper\Tests\Fixtures\ObjectDest;
use Backbrain\Automapper\Tests\Fixtures\ObjectDestSameType;
use Backbrain\Automapper\Tests\Fixtures\ObjectSrc;
use Backbrain\Automapper\Tests\Fixtures\ScalarDest;
use Backbrain\Automapper\Tests\Fixtures\ScalarDestSnakeCase;
use Backbrain\Automapper\Tests\Fixtures\ScalarDestWithAnotherString;
use Backbrain\Automapper\Tests\Fixtures\ScalarSrc;
use PHPUnit\Framework\TestCase;

class MapTest extends TestCase
{
    public function testMissingMapException()
    {
        $this->expectException(MapperException::class);
        $this->expectExceptionCode(MapperException::MISSING_MAP);

        $autoMapper = new AutoMapper(new MapperConfiguration());

        $source = new ScalarSrc('John Doe', 30, 1.75);
        $autoMapper->map($source, ScalarDest::class);
    }

    public function testDefaultMappingBehavior()
    {
        $config = new MapperConfiguration(fn (Config $config) => $config
            ->createMap(ScalarSrc::class, ScalarDest::class)
        );

        $autoMapper = $config->createMapper();

        $source = new ScalarSrc('John Doe', 30, 1.75);
        $destination = $autoMapper->map($source, ScalarDest::class);

        $this->assertInstanceOf(ScalarDest::class, $destination);
        $this->assertEquals($source->aString, $destination->aString);
        $this->assertEquals($source->anInt, $destination->anInt);
        $this->assertEquals($source->aFloat, $destination->aFloat);
        $this->assertEquals($source->aBool, $destination->aBool);
        $this->assertEquals($source->anArray, $destination->anArray);
    }

    public function testDefaultMappingBehaviorWithIgnoredMember()
    {
        $config = new MapperConfiguration(fn (Config $config) => $config
            ->createMap(ScalarSrc::class, ScalarDest::class)
            ->forMember('aString', fn (Options $opts) => $opts->ignore())
        );

        $autoMapper = $config->createMapper();

        $source = new ScalarSrc('John Doe', 30, 1.75);
        $destObj = new ScalarDest();
        $destObj->aString = 'Ignored';

        $autoMapper->mutate($source, $destObj);

        $this->assertEquals('Ignored', $destObj->aString);
        $this->assertEquals($source->anInt, $destObj->anInt);
        $this->assertEquals($source->aFloat, $destObj->aFloat);
        $this->assertEquals($source->aBool, $destObj->aBool);
    }

    public function testMapFrom()
    {
        $config = new MapperConfiguration(fn (Config $config) => $config
            ->createMap(ScalarSrc::class, ScalarDestWithAnotherString::class)
            ->forMember('anotherString', fn (Options $opts) => $opts->mapFrom(fn (ScalarSrc $source) => sprintf('%s (%d)', $source->aString, $source->anInt)))
        );

        $autoMapper = $config->createMapper();

        $source = new ScalarSrc('John Doe', 30, 1.75);
        $destination = $autoMapper->map($source, ScalarDestWithAnotherString::class);

        $this->assertInstanceOf(ScalarDestWithAnotherString::class, $destination);
        $this->assertEquals(sprintf('%s (%d)', $source->aString, $source->anInt), $destination->anotherString);
    }

    public function testDestinationClassNotFoundExceptionShouldBeThrown()
    {
        $this->expectException(MapperException::class);
        $this->expectExceptionCode(MapperException::CLASS_NOT_FOUND);

        $config = new MapperConfiguration(fn (Config $config) => $config
            ->createMap(ScalarSrc::class, 'NonExistentClass')
        );

        $autoMapper = $config->createMapper();

        $source = new ScalarSrc('John Doe', 30, 1.75);
        $autoMapper->map($source, 'NonExistentClass');
    }

    public function testDefaultMappingBehaviorWithNamingConventions()
    {
        $config = new MapperConfiguration(fn (Config $config) => $config
            ->createMap(ScalarSrc::class, ScalarDestSnakeCase::class)
            ->destinationMemberNamingConvention(SnakeCaseNamingConvention::class)
            ->sourceMemberNamingConvention(CamelCaseNamingConvention::class)
        );

        $autoMapper = $config->createMapper();

        $source = new ScalarSrc('John Doe', 30, 1.75);
        $destination = $autoMapper->map($source, ScalarDestSnakeCase::class);

        $this->assertInstanceOf(ScalarDestSnakeCase::class, $destination);
        $this->assertEquals($source->aString, $destination->a_string);
        $this->assertEquals($source->anInt, $destination->an_int);
        $this->assertEquals($source->aFloat, $destination->a_float);
        $this->assertEquals($source->aBool, $destination->a_bool);
    }

    public function testDefaultMappingBehaviorWithObject()
    {
        $config = new MapperConfiguration(fn (Config $config) => $config
            ->createMap(ScalarSrc::class, ScalarDest::class)
        );

        $autoMapper = $config->createMapper();

        $source = new ScalarSrc('John Doe', 30, 1.75);
        $destObj = new ScalarDest();
        $autoMapper->mutate($source, $destObj);

        $this->assertEquals($source->aString, $destObj->aString);
        $this->assertEquals($source->anInt, $destObj->anInt);
        $this->assertEquals($source->aFloat, $destObj->aFloat);
        $this->assertEquals($source->aBool, $destObj->aBool);
    }

    public function testObjectMappingWithNamingConvention()
    {
        $config = new MapperConfiguration(fn (Config $config) => $config
            ->createMap(ObjectSrc::class, ObjectDest::class)
            ->createMap(ScalarSrc::class, ScalarDestSnakeCase::class)
            ->destinationMemberNamingConvention(SnakeCaseNamingConvention::class)
            ->sourceMemberNamingConvention(CamelCaseNamingConvention::class)
        );

        $autoMapper = $config->createMapper();

        $srcPropValue = new ScalarSrc();
        $source = new ObjectSrc($srcPropValue);
        $destination = $autoMapper->map($source, ObjectDest::class);

        $this->assertEquals($srcPropValue->aString, $destination->getObj()->a_string);
        $this->assertEquals($srcPropValue->anInt, $destination->getObj()->an_int);
        $this->assertEquals($srcPropValue->aFloat, $destination->getObj()->a_float);
        $this->assertEquals($srcPropValue->aBool, $destination->getObj()->a_bool);
    }

    public function testObjectMappingDestSameType()
    {
        $config = new MapperConfiguration(fn (Config $config) => $config
            ->createMap(ObjectSrc::class, ObjectDestSameType::class)
        );

        $autoMapper = $config->createMapper();

        $source = new ObjectSrc(new ScalarSrc());
        $destination = $autoMapper->map($source, ObjectDestSameType::class);

        $this->assertSame($source->getObj(), $destination->getObj());
    }
}
