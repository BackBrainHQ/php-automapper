<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract\Builder;

use Backbrain\Automapper\Contract\ValueResolverInterface;

interface MemberOptionsBuilderInterface
{
    /**
     * @param ValueResolverInterface|callable(object $source):mixed $valueProvider
     */
    public function mapFrom(ValueResolverInterface|callable $valueProvider): MemberOptionsBuilderInterface;

    public function ignore(bool $ignore = true): MemberOptionsBuilderInterface;

    /**
     * @param callable(mixed $source):bool $condition
     */
    public function condition(callable $condition): MemberOptionsBuilderInterface;

    public function nullSubstitute(mixed $value): MemberOptionsBuilderInterface;
}
