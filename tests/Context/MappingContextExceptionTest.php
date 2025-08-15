<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Context;

use Backbrain\Automapper\AutoMapper;
use Backbrain\Automapper\Contract\Builder\Config;
use Backbrain\Automapper\Exceptions\MapperException;
use Backbrain\Automapper\MapperConfiguration;
use Backbrain\Automapper\Tests\Fixtures\ScalarDest;
use Backbrain\Automapper\Tests\Fixtures\ScalarSrc;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class MappingContextExceptionTest extends TestCase
{
    public function testContextIsAppendedForMissingMap(): void
    {
        $autoMapper = new AutoMapper(new MapperConfiguration());

        $source = new ScalarSrc('John Doe', 30, 1.75);

        try {
            $autoMapper->map($source, ScalarDest::class);
            self::fail('Expected MapperException to be thrown');
        } catch (MapperException $e) {
            self::assertSame(MapperException::MISSING_MAP, $e->getCode());
            $msg = $e->getMessage();
            // Expect the context suffix
            self::assertStringContainsString('Context: ', $msg);
            // Root path and depth
            self::assertStringContainsString('path=(root)', $msg);
            self::assertStringContainsString('depth=0', $msg);
            // Source and destination types present in context
            self::assertStringContainsString('source='.ScalarSrc::class, $msg);
            self::assertStringContainsString('dest='.ScalarDest::class, $msg);
            // No applied map expected for missing map case
            self::assertStringNotContainsString('map=', $msg);
        }
    }

    public function testContextIncludesAppliedMapWhenDestinationClassMissing(): void
    {
        $config = new MapperConfiguration(fn (Config $config) => $config
            ->createMap(ScalarSrc::class, 'NonExistentClass')
        );

        $autoMapper = $config->createMapper();
        $source = new ScalarSrc('Jane', 42, 1.65);

        try {
            $autoMapper->map($source, 'NonExistentClass');
            self::fail('Expected MapperException to be thrown');
        } catch (MapperException $e) {
            self::assertSame(MapperException::CLASS_NOT_FOUND, $e->getCode());
            $msg = $e->getMessage();
            self::assertStringContainsString('Context: ', $msg);
            self::assertStringContainsString('path=(root)', $msg);
            self::assertStringContainsString('depth=0', $msg);
            self::assertStringContainsString('source='.ScalarSrc::class, $msg);
            self::assertStringContainsString('dest=NonExistentClass', $msg);
            // Applied map id should be present when a map was selected prior to instantiation
            self::assertStringContainsString('map='.ScalarSrc::class.' => NonExistentClass', $msg);
        }
    }

    public function testIllegalTypeExpressionInIterableAppendsContext(): void
    {
        $autoMapper = new AutoMapper(new MapperConfiguration());
        $iterable = [new ScalarSrc('John', 30, 1.80)];

        try {
            // Intentionally illegal type expression containing a space
            $autoMapper->mapIterable($iterable, 'Array< Backbrain\\Automapper\\Tests\\Fixtures\\ScalarDest >');
            self::fail('Expected MapperException to be thrown');
        } catch (MapperException $e) {
            self::assertSame(MapperException::ILLEGAL_TYPE_EXPRESSION, $e->getCode());
            $msg = $e->getMessage();
            self::assertStringContainsString('Context: ', $msg);
            self::assertStringContainsString('path=(root)', $msg);
            self::assertStringContainsString('depth=0', $msg);
            // source type for iterable context will be array
            self::assertStringContainsString('source=array', $msg);
            // destination is the raw (trimmed) expression from MappingContext::root
            self::assertStringContainsString('dest=Array< Backbrain\\Automapper\\Tests\\Fixtures\\ScalarDest >', $msg);
        }
    }
}
