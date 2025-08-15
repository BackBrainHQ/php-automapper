<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Context;

use Backbrain\Automapper\Contract\Builder\Config;
use Backbrain\Automapper\Contract\Builder\Options;
use Backbrain\Automapper\Exceptions\MapperException;
use Backbrain\Automapper\MapperConfiguration;
use Backbrain\Automapper\Tests\Fixtures\ScalarDest;
use Backbrain\Automapper\Tests\Fixtures\ScalarSrc;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class MutateContextExceptionTest extends TestCase
{
    public function testMutateAppendsContextWhenMemberMappingFails(): void
    {
        $config = new MapperConfiguration(fn (Config $config) => $config
            ->createMap(ScalarSrc::class, ScalarDest::class)
            // Force a member mapping that cannot be resolved: array -> string without converter
            ->forMember('aString', fn (Options $opts) => $opts->mapFrom(fn (ScalarSrc $source) => $source->anArray))
        );

        $mapper = $config->createMapper();

        $source = new ScalarSrc('John', 1, 1.0);
        $dest = new ScalarDest();

        try {
            $mapper->mutate($source, $dest);
            self::fail('Expected MapperException to be thrown');
        } catch (MapperException $e) {
            self::assertSame(MapperException::MISSING_MAP, $e->getCode());
            $msg = $e->getMessage();
            self::assertStringContainsString('Context: ', $msg);
            self::assertStringContainsString('path=(root)', $msg);
            self::assertStringContainsString('depth=0', $msg);
            self::assertStringContainsString('source='.ScalarSrc::class, $msg);
            self::assertStringContainsString('dest='.ScalarDest::class, $msg);
            self::assertStringContainsString('map='.ScalarSrc::class.' => '.ScalarDest::class, $msg);
        }
    }
}
