<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Functional;

use Backbrain\Automapper\Contract\Builder\Config;
use Backbrain\Automapper\MapperConfiguration;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class MapIterableErrorsTest extends TestCase
{
    public function testMapIterableThrowsWhenConverterReturnsNonIterable(): void
    {
        $config = new MapperConfiguration(fn (Config $config) => $config
            ->createMap('array', 'string')
            ->convertUsing(fn (array $source): string => 'not-iterable')
        );

        $mapper = $config->createMapper();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Type is not a collection');

        // Destination type is not a collection (plain string), but a map exists and returns a non-iterable
        $mapper->mapIterable([1, 2, 3], 'string');
    }
}
