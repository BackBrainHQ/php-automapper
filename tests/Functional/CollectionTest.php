<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Functional;

use Backbrain\Automapper\Contract\Builder\ProfileBuilderInterface;
use Backbrain\Automapper\Converter\Naming\CamelCaseNamingConvention;
use Backbrain\Automapper\Converter\Naming\SnakeCaseNamingConvention;
use Backbrain\Automapper\Helper\Type;
use Backbrain\Automapper\MapperConfiguration;
use Backbrain\Automapper\Tests\Fixtures\ObjectDest;
use Backbrain\Automapper\Tests\Fixtures\ObjectSrc;
use Backbrain\Automapper\Tests\Fixtures\ScalarDest;
use Backbrain\Automapper\Tests\Fixtures\ScalarDestSnakeCase;
use Backbrain\Automapper\Tests\Fixtures\ScalarSrc;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    public function testMapIterableIndexed()
    {
        $config = new MapperConfiguration(fn (ProfileBuilderInterface $config) => $config
            ->createMap(ScalarSrc::class, ScalarDest::class)
            ->createMap(ScalarSrc::class, ScalarDestSnakeCase::class)
            ->destinationMemberNamingConvention(SnakeCaseNamingConvention::class)
            ->sourceMemberNamingConvention(CamelCaseNamingConvention::class)
            ->createMap(ObjectSrc::class, ObjectDest::class)
            ->createMap('int', 'string')
            ->convertUsing(fn (int $source) => (string) $source)
        );

        $autoMapper = $config->createMapper();

        $sources = [
            new ScalarSrc('John Doe', 30, 1.75),
            new ObjectSrc(new ScalarSrc()),
            1,
        ];

        $destination = $autoMapper->mapIterable($sources, Type::arrayOf(ScalarDest::class, ObjectDest::class, 'string'));

        $this->assertIsIterable($destination);
        $this->assertCount(count($sources), $destination);
        $this->assertInstanceOf(ScalarDest::class, $destination[0]);
        $this->assertInstanceOf(ObjectDest::class, $destination[1]);
        $this->assertIsString($destination[2]);

        $this->assertEquals($sources[0]->aString, $destination[0]->aString);
        $this->assertEquals($sources[0]->anInt, $destination[0]->anInt);
        $this->assertEquals($sources[0]->aFloat, $destination[0]->aFloat);
        $this->assertEquals($sources[0]->aBool, $destination[0]->aBool);

        $this->assertEquals($sources[1]->getObj()->aString, $destination[1]->getObj()->a_string);
        $this->assertEquals($sources[1]->getObj()->anInt, $destination[1]->getObj()->an_int);
        $this->assertEquals($sources[1]->getObj()->aFloat, $destination[1]->getObj()->a_float);
        $this->assertEquals($sources[1]->getObj()->aBool, $destination[1]->getObj()->a_bool);

        $this->assertEquals('1', $destination[2]);
    }

    public function testMapIterableKey()
    {
        $config = new MapperConfiguration(fn (ProfileBuilderInterface $config) => $config
            ->createMap(ScalarSrc::class, ScalarDest::class)
            ->createMap(ScalarSrc::class, ScalarDestSnakeCase::class)
            ->destinationMemberNamingConvention(SnakeCaseNamingConvention::class)
            ->sourceMemberNamingConvention(CamelCaseNamingConvention::class)
            ->createMap(ObjectSrc::class, ObjectDest::class)
            ->createMap('int', 'string')
            ->convertUsing(fn (int $source) => (string) $source)
        );

        $autoMapper = $config->createMapper();

        $sources = [];
        $sources['a'] = new ScalarSrc('John Doe', 30, 1.75);
        $sources['b'] = new ObjectSrc(new ScalarSrc());
        $sources['c'] = 1;

        $destination = $autoMapper->mapIterable($sources, Type::arrayOf(ScalarDest::class, ObjectDest::class, 'string'));
        $this->assertArrayHasKey('a', $destination);
        $this->assertArrayHasKey('b', $destination);
        $this->assertArrayHasKey('c', $destination);

        $this->assertInstanceOf(ScalarDest::class, $destination['a']);
        $this->assertInstanceOf(ObjectDest::class, $destination['b']);
        $this->assertIsString($destination['c']);

        $this->assertEquals($sources['a']->aString, $destination['a']->aString);
        $this->assertEquals($sources['a']->anInt, $destination['a']->anInt);
        $this->assertEquals($sources['a']->aFloat, $destination['a']->aFloat);
        $this->assertEquals($sources['a']->aBool, $destination['a']->aBool);

        $this->assertEquals($sources['b']->getObj()->aString, $destination['b']->getObj()->a_string);
        $this->assertEquals($sources['b']->getObj()->anInt, $destination['b']->getObj()->an_int);
        $this->assertEquals($sources['b']->getObj()->aFloat, $destination['b']->getObj()->a_float);
        $this->assertEquals($sources['b']->getObj()->aBool, $destination['b']->getObj()->a_bool);

        $this->assertEquals('1', $destination['c']);
    }

    public function testMapIterableWithMap()
    {
        $config = new MapperConfiguration(fn (ProfileBuilderInterface $config) => $config
            ->createMap(ScalarSrc::class, ScalarDest::class)
            ->createMap(ScalarSrc::class, ScalarDestSnakeCase::class)
            ->destinationMemberNamingConvention(SnakeCaseNamingConvention::class)
            ->sourceMemberNamingConvention(CamelCaseNamingConvention::class)
            ->createMap(ObjectSrc::class, ObjectDest::class)
            ->createMap('int', 'string')
            ->convertUsing(fn (int $source) => sprintf('idx_%d', $source))
            ->createMap('array', Collection::class)
            ->constructUsing(fn (array $source) => new ArrayCollection())
        );

        $autoMapper = $config->createMapper();

        $sources = [
            new ScalarSrc('John Doe', 30, 1.75),
            new ObjectSrc(new ScalarSrc()),
            '1',
        ];

        $destType = Type::collectionOf(Collection::class, ['string'], [ScalarDest::class, ObjectDest::class, 'string']);
        /** @var ArrayCollection $destination */
        $destination = $autoMapper->mapIterable($sources, $destType);

        $this->assertInstanceOf(ArrayCollection::class, $destination);
        $this->assertSame(['idx_0', 'idx_1', 'idx_2'], $destination->getKeys());

        $this->assertIsIterable($destination);
        $this->assertCount(count($sources), $destination);
        $this->assertInstanceOf(ScalarDest::class, $destination['idx_0']);
        $this->assertInstanceOf(ObjectDest::class, $destination['idx_1']);
        $this->assertIsString($destination['idx_2']);

        $this->assertEquals($sources[0]->aString, $destination['idx_0']->aString);
        $this->assertEquals($sources[0]->anInt, $destination['idx_0']->anInt);
        $this->assertEquals($sources[0]->aFloat, $destination['idx_0']->aFloat);
        $this->assertEquals($sources[0]->aBool, $destination['idx_0']->aBool);

        $this->assertEquals($sources[1]->getObj()->aString, $destination['idx_1']->getObj()->a_string);
        $this->assertEquals($sources[1]->getObj()->anInt, $destination['idx_1']->getObj()->an_int);
        $this->assertEquals($sources[1]->getObj()->aFloat, $destination['idx_1']->getObj()->a_float);
        $this->assertEquals($sources[1]->getObj()->aBool, $destination['idx_1']->getObj()->a_bool);

        $this->assertEquals('1', $destination['idx_2']);
    }
}
