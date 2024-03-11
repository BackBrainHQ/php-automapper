<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Functional;

use Backbrain\Automapper\Contract\Builder\MemberOptionsBuilderInterface;
use Backbrain\Automapper\Contract\Builder\ProfileBuilderInterface;
use Backbrain\Automapper\MapperConfiguration;
use Backbrain\Automapper\Profiles\ScalarToStringProfile;
use Backbrain\Automapper\Tests\Fixtures\ScalarDest;
use Backbrain\Automapper\Tests\Fixtures\ScalarDestAllString;
use Backbrain\Automapper\Tests\Fixtures\ScalarSrc;
use PHPUnit\Framework\TestCase;

class ExtensibilityTest extends TestCase
{
    public function testBeforeMap()
    {
        $config = new MapperConfiguration(fn (ProfileBuilderInterface $config) => $config
            ->createMap(ScalarSrc::class, ScalarDest::class)
            ->beforeMap(fn (ScalarSrc $source, ScalarDest $destination) => $source->aString = 'BeforeMap')
        );

        $autoMapper = $config->createMapper();

        $source = new ScalarSrc('John Doe', 30, 1.75);
        $destination = $autoMapper->map($source, ScalarDest::class);

        $this->assertEquals('BeforeMap', $destination->aString);
    }

    public function testAfterMap()
    {
        $config = new MapperConfiguration(fn (ProfileBuilderInterface $config) => $config
            ->createMap(ScalarSrc::class, ScalarDest::class)
            ->afterMap(fn (ScalarSrc $source, ScalarDest $destination) => $destination->aString = 'AfterMap')
        );

        $autoMapper = $config->createMapper();

        $source = new ScalarSrc('John Doe', 30, 1.75);
        $destination = $autoMapper->map($source, ScalarDest::class);

        $this->assertEquals('AfterMap', $destination->aString);
    }

    public function testCondition()
    {
        $config = new MapperConfiguration(fn (ProfileBuilderInterface $config) => $config
            ->createMap(ScalarSrc::class, ScalarDest::class)
            ->forMember('aString', fn (MemberOptionsBuilderInterface $opts) => $opts->condition(fn (ScalarSrc $source) => 'John Doe' === $source->aString))
        );

        $autoMapper = $config->createMapper();

        $source = new ScalarSrc('John Doe', 30, 1.75);
        $destination = $autoMapper->map($source, ScalarDest::class);

        $this->assertEquals('John Doe', $destination->aString);

        $source = new ScalarSrc('Jane Doe', 30, 1.75);
        $destination = $autoMapper->map($source, ScalarDest::class);

        $this->assertNotEquals('Jane Doe', $destination->aString);
    }

    public function testNullSubstitute()
    {
        $config = new MapperConfiguration(fn (ProfileBuilderInterface $config) => $config
            ->createMap(ScalarSrc::class, ScalarDest::class)
            ->forMember('aNullString', fn (MemberOptionsBuilderInterface $opts) => $opts->nullSubstitute('Substitute'))
        );

        $autoMapper = $config->createMapper();

        $source = new ScalarSrc('John Doe', 30, 1.75);
        $destination = $autoMapper->map($source, ScalarDest::class);
        $this->assertEquals('Substitute', $destination->aNullString);
    }

    public function testScalarToStringConversion()
    {
        $config = new MapperConfiguration(fn (ProfileBuilderInterface $config) => $config
            ->addProfile(new ScalarToStringProfile())
            ->createMap(ScalarSrc::class, ScalarDestAllString::class)
        );
        $autoMapper = $config->createMapper();

        $source = new ScalarSrc('John Doe', 30, 1.75);
        $destination = $autoMapper->map($source, ScalarDestAllString::class);

        $this->assertInstanceOf(ScalarDestAllString::class, $destination);
        $this->assertEquals("$destination->aString", $source->aString);
        $this->assertEquals("$destination->anInt", $source->anInt);
        $this->assertEquals("$destination->aFloat", $source->aFloat);
        $this->assertEquals('true', $source->aBool);
    }
}
