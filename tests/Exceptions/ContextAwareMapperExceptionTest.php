<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Exceptions;

use Backbrain\Automapper\Context\MappingContext;
use Backbrain\Automapper\Exceptions\ContextAwareMapperException;
use Backbrain\Automapper\Exceptions\MapperException;
use Backbrain\Automapper\Tests\Fixtures\ScalarDest;
use Backbrain\Automapper\Tests\Fixtures\ScalarSrc;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class ContextAwareMapperExceptionTest extends TestCase
{
    public function testFromMapperExceptionWrapsAndCarriesContext(): void
    {
        $orig = MapperException::newIllegalTypeException('Foo');

        $src = new ScalarSrc('John Doe', 30, 1.75);
        $ctx = MappingContext::root($src, ScalarDest::class);

        $wrapped = ContextAwareMapperException::fromMapperException($orig, $ctx);

        self::assertInstanceOf(ContextAwareMapperException::class, $wrapped);
        self::assertSame(MapperException::ILLEGAL_TYPE_EXPRESSION, $wrapped->getCode());
        self::assertSame($ctx, $wrapped->getContext());
        self::assertStringContainsString('Context: ', $wrapped->getMessage());
    }

    public function testFromMapperExceptionReturnsSameInstanceWhenAlreadyContextAware(): void
    {
        $ctx1 = MappingContext::root(new ScalarSrc('Jane', 42, 1.65), ScalarDest::class);
        $e = new ContextAwareMapperException($ctx1, 'Problem occurred', 123);

        $ctx2 = MappingContext::root(new ScalarSrc('Jake', 21, 1.80), ScalarDest::class);
        $wrapped = ContextAwareMapperException::fromMapperException($e, $ctx2);

        self::assertSame($e, $wrapped);
        self::assertSame($ctx1, $wrapped->getContext());
        self::assertSame(123, $wrapped->getCode());
        self::assertSame('Problem occurred', $wrapped->getMessage());
    }
}
