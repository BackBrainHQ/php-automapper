<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Functional;

use Backbrain\Automapper\Contract\Builder\Config;
use Backbrain\Automapper\Contract\Builder\Options;
use Backbrain\Automapper\Converter\Naming\CamelCaseNamingConvention;
use Backbrain\Automapper\Converter\Naming\SnakeCaseNamingConvention;
use Backbrain\Automapper\MapperConfiguration;
use Backbrain\Automapper\Tests\Fixtures\ArrayDest;
use Backbrain\Automapper\Tests\Fixtures\ArraySrc;
use Backbrain\Automapper\Tests\Fixtures\ScalarDest;
use Backbrain\Automapper\Tests\Fixtures\ScalarDestInterface;
use Backbrain\Automapper\Tests\Fixtures\ScalarDestSnakeCase;
use Backbrain\Automapper\Tests\Fixtures\ScalarSrc;
use PHPUnit\Framework\TestCase;

class MapArrayTest extends TestCase
{
    public function testMappingArrayOfStringToUntypedArray()
    {
        $config = new MapperConfiguration(fn (Config $config) => $config
            ->createMap(ArraySrc::class, ArrayDest::class)
            ->forMember('anArrayOfMixed', fn (Options $opts) => $opts->mapFrom(fn (ArraySrc $source) => $source->srcArrayOfStrings))
            ->forMember('anUntypedArray', fn (Options $opts) => $opts->mapFrom(fn (ArraySrc $source) => $source->srcArrayOfStrings))
        );

        $autoMapper = $config->createMapper();

        $source = new ArraySrc(srcArrayOfStrings: ['aString', 'bString', 'cString']);
        $destination = $autoMapper->map($source, ArrayDest::class);

        $this->assertEquals($source->srcArrayOfStrings, $destination->anArrayOfMixed);
        $this->assertEquals($source->srcArrayOfStrings, $destination->anUntypedArray);
    }

    public function testMappingArrayOfStringToTypedArray()
    {
        $config = new MapperConfiguration(fn (Config $config) => $config
            ->createMap(ArraySrc::class, ArrayDest::class)
            ->forMember('anArrayOfMixed', fn (Options $opts) => $opts->mapFrom(fn (ArraySrc $source) => $source->srcArrayOfStrings))
            ->forMember('anUntypedArray', fn (Options $opts) => $opts->mapFrom(fn (ArraySrc $source) => $source->srcArrayOfStrings))
        );

        $autoMapper = $config->createMapper();

        $source = new ArraySrc(srcArrayOfStrings: ['aString', 'bString', 'cString']);
        $destination = $autoMapper->map($source, ArrayDest::class);

        $this->assertEquals($source->srcArrayOfStrings, $destination->anArrayOfMixed);
        $this->assertEquals($source->srcArrayOfStrings, $destination->anUntypedArray);
    }

    public function testMappingArrayOfStringInt()
    {
        $config = new MapperConfiguration(fn (Config $config) => $config
            ->createMap(ArraySrc::class, ArrayDest::class)
            ->forMember('anArrayOfStringInt', fn (Options $opts) => $opts->mapFrom(fn (ArraySrc $source) => $source->srcArrayOfStringInt))
        );

        $autoMapper = $config->createMapper();

        $source = new ArraySrc(srcArrayOfStringInt: [1, '2', 3]);
        $destination = $autoMapper->map($source, ArrayDest::class);

        $this->assertEquals($source->srcArrayOfStringInt, $destination->anArrayOfStringInt);
    }

    public function testMappingArrayOfObject()
    {
        $config = new MapperConfiguration(fn (Config $config) => $config
            ->createMap(ArraySrc::class, ArrayDest::class)
            ->forMember('anArrayOfScalarDestSnakeCase', fn (Options $opts) => $opts->mapFrom(fn (ArraySrc $source) => $source->srcArrayOfScalarSrc))
            ->createMap(ScalarSrc::class, ScalarDestSnakeCase::class)
            ->destinationMemberNamingConvention(SnakeCaseNamingConvention::class)
            ->sourceMemberNamingConvention(CamelCaseNamingConvention::class)
        );

        $autoMapper = $config->createMapper();

        $source = new ArraySrc(srcArrayOfScalarSrc: [
            new ScalarSrc('aString', 1, 1.0, true),
            new ScalarSrc('bString', 2, 2.0, false),
            new ScalarSrc('cString', 3, 3.0, true),
        ]);

        $destination = $autoMapper->map($source, ArrayDest::class);

        $this->assertCount(count($source->srcArrayOfScalarSrc), $destination->anArrayOfScalarDestSnakeCase);
        foreach ($source->srcArrayOfScalarSrc as $i => $src) {
            $this->assertInstanceOf(ScalarDestSnakeCase::class, $destination->anArrayOfScalarDestSnakeCase[$i]);
            $this->assertEquals($src->aString, $destination->anArrayOfScalarDestSnakeCase[$i]->a_string);
            $this->assertEquals($src->anInt, $destination->anArrayOfScalarDestSnakeCase[$i]->an_int);
            $this->assertEquals($src->aFloat, $destination->anArrayOfScalarDestSnakeCase[$i]->a_float);
            $this->assertEquals($src->aBool, $destination->anArrayOfScalarDestSnakeCase[$i]->a_bool);
        }
    }

    public function testMappingArrayOfObjectUsingTypeFactory()
    {
        $config = new MapperConfiguration(fn (Config $config) => $config
            ->createMap(ArraySrc::class, ArrayDest::class)
                ->forMember('anArrayOfScalarDestInterface', fn (Options $opts) => $opts->mapFrom(fn (ArraySrc $source) => $source->srcArrayOfScalarSrc))
            ->createMap(ScalarSrc::class, ScalarDest::class)
                ->forMember('aString', fn (Options $opts) => $opts->ignore()) // expected to be overridden
                ->forMember('aFloat', fn (Options $opts) => $opts->mapFrom(fn (ScalarSrc $source) => $source->aFloat))
                ->constructUsing(fn (ScalarSrc $source) => (new ScalarDest())->setUnmapped('wrong'))
            ->createMap(ScalarSrc::class, ScalarDestInterface::class)
                ->as(ScalarDest::class)
                ->forMember('aString', fn (Options $opts) => $opts->mapFrom(fn (ScalarSrc $source) => $source->aString))
                ->constructUsing(fn (ScalarSrc $source) => (new ScalarDest())->setUnmapped('factory'))
        );

        $autoMapper = $config->createMapper();

        $source = new ArraySrc(srcArrayOfScalarSrc: [
            new ScalarSrc('aString', 1, 1.0, true),
            new ScalarSrc('bString', 2, 2.0, false),
            new ScalarSrc('cString', 3, 3.0, true),
        ]);

        $destination = $autoMapper->map($source, ArrayDest::class);

        $this->assertCount(count($source->srcArrayOfScalarSrc), $destination->anArrayOfScalarDestInterface);
        foreach ($source->srcArrayOfScalarSrc as $i => $src) {
            $this->assertInstanceOf(ScalarDest::class, $destination->anArrayOfScalarDestInterface[$i]);
            $this->assertEquals('factory', $destination->anArrayOfScalarDestInterface[$i]->getUnmapped());
            $this->assertEquals($src->aString, $destination->anArrayOfScalarDestInterface[$i]->getAString());
            $this->assertEquals($src->anInt, $destination->anArrayOfScalarDestInterface[$i]->getAnInt());
            $this->assertEquals($src->aFloat, $destination->anArrayOfScalarDestInterface[$i]->getAFloat());
            $this->assertEquals($src->anArray, $destination->anArrayOfScalarDestInterface[$i]->getAnArray());
        }
    }

    public function testMappingTypeFactoryFromMappedBy()
    {
        $config = new MapperConfiguration(fn (Config $config) => $config
            ->createMap(ArraySrc::class, ArrayDest::class)
                ->forMember('anArrayOfScalarDestInterface', fn (Options $opts) => $opts->mapFrom(fn (ArraySrc $source) => $source->srcArrayOfScalarSrc))
            ->createMap(ScalarSrc::class, ScalarDest::class)
                ->forMember('aString', fn (Options $opts) => $opts->ignore()) // expected to be overridden
                ->forMember('aFloat', fn (Options $opts) => $opts->mapFrom(fn (ScalarSrc $source) => $source->aFloat))
                ->constructUsing(fn (ScalarSrc $source) => (new ScalarDest())->setUnmapped('factory'))
            ->createMap(ScalarSrc::class, ScalarDestInterface::class)
                ->as(ScalarDest::class)
                ->forMember('aString', fn (Options $opts) => $opts->mapFrom(fn (ScalarSrc $source) => $source->aString))
        );

        $autoMapper = $config->createMapper();

        $source = new ArraySrc(srcArrayOfScalarSrc: [
            new ScalarSrc('aString', 1, 1.0, true),
            new ScalarSrc('bString', 2, 2.0, false),
            new ScalarSrc('cString', 3, 3.0, true),
        ]);

        $destination = $autoMapper->map($source, ArrayDest::class);

        $this->assertCount(count($source->srcArrayOfScalarSrc), $destination->anArrayOfScalarDestInterface);
        foreach ($source->srcArrayOfScalarSrc as $i => $src) {
            $this->assertInstanceOf(ScalarDest::class, $destination->anArrayOfScalarDestInterface[$i]);
            $this->assertEquals('factory', $destination->anArrayOfScalarDestInterface[$i]->getUnmapped());
        }
    }

    public function testMappingDefaultTypeFactoryFromMappedBy()
    {
        $config = new MapperConfiguration(fn (Config $config) => $config
            ->createMap(ArraySrc::class, ArrayDest::class)
                ->forMember('anArrayOfScalarDestInterface', fn (Options $opts) => $opts->mapFrom(fn (ArraySrc $source) => $source->srcArrayOfScalarSrc))
            ->createMap(ScalarSrc::class, ScalarDest::class)
                ->forMember('aString', fn (Options $opts) => $opts->ignore()) // expected to be overridden
                ->forMember('aFloat', fn (Options $opts) => $opts->mapFrom(fn (ScalarSrc $source) => $source->aFloat))
            ->createMap(ScalarSrc::class, ScalarDestInterface::class)
                ->as(ScalarDest::class)
                ->forMember('aString', fn (Options $opts) => $opts->mapFrom(fn (ScalarSrc $source) => $source->aString))
        );

        $autoMapper = $config->createMapper();

        $source = new ArraySrc(srcArrayOfScalarSrc: [
            new ScalarSrc('aString', 1, 1.0, true),
            new ScalarSrc('bString', 2, 2.0, false),
            new ScalarSrc('cString', 3, 3.0, true),
        ]);

        $destination = $autoMapper->map($source, ArrayDest::class);

        $this->assertCount(count($source->srcArrayOfScalarSrc), $destination->anArrayOfScalarDestInterface);
        foreach ($source->srcArrayOfScalarSrc as $i => $src) {
            $this->assertInstanceOf(ScalarDest::class, $destination->anArrayOfScalarDestInterface[$i]);
            $this->assertNull($destination->anArrayOfScalarDestInterface[$i]->getUnmapped());
        }
    }
}
