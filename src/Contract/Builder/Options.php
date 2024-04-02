<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract\Builder;

use Backbrain\Automapper\Contract\ResolutionContextInterface;
use Backbrain\Automapper\Contract\ValueResolverInterface;

interface Options
{
    /**
     * It takes a callable or a ValueResolverInterface as a parameter.
     * Example:
     * ```php
     * ->mapFrom(function (object $source, ResolutionContextInterface $context): mixed {
     *    return $source->getFoo();
     * });
     * ```.
     */
    public function mapFrom(ValueResolverInterface|callable $valueProvider): Options;

    public function ignore(bool $ignore = true): Options;

    /**
     * It takes a callable as a parameter. Only if the callable returns true, the member will be mapped.
     * Example:
     * ```php
     * ->condition(function (object $source): bool {
     *   return $source->getFoo() !== null;
     * });
     * ```.
     */
    public function condition(callable $condition): Options;

    public function nullSubstitute(mixed $value): Options;
}
