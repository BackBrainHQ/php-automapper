<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract\Builder;

use Backbrain\Automapper\Contract\ResolutionContextInterface;
use Backbrain\Automapper\Contract\ValueResolverInterface;

interface Options
{
    /**
     * @param ValueResolverInterface|callable(object $source, ResolutionContextInterface $context):mixed $valueProvider
     */
    public function mapFrom(ValueResolverInterface|callable $valueProvider): Options;

    public function ignore(bool $ignore = true): Options;

    /**
     * @param callable(mixed $source):bool $condition
     */
    public function condition(callable $condition): Options;

    public function nullSubstitute(mixed $value): Options;
}
