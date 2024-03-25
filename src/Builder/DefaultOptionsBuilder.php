<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Builder;

use Backbrain\Automapper\Contract\Builder\Options;
use Backbrain\Automapper\Contract\MemberInterface;
use Backbrain\Automapper\Contract\ValueResolverInterface;
use Backbrain\Automapper\Helper\Func;
use Backbrain\Automapper\Model\Member;

class DefaultOptionsBuilder implements Options
{
    private string $destinationPropertyPath;

    private ?ValueResolverInterface $valueProvider = null;

    private bool $ignore = false;

    /**
     * @var callable(mixed):bool|null
     */
    private mixed $conditionFn = null;

    private mixed $nullSubstitute = null;

    public function __construct(string $destinationPropertyPath)
    {
        $this->destinationPropertyPath = $destinationPropertyPath;
    }

    public function build(): MemberInterface
    {
        return new Member(
            destinationPropertyPath: $this->destinationPropertyPath,
            valueProvider: $this->valueProvider,
            ignore: $this->ignore,
            nullSubstitute: $this->nullSubstitute,
            condition: $this->conditionFn,
        );
    }

    public function mapFrom(ValueResolverInterface|callable $valueProvider): Options
    {
        $this->valueProvider = Func::valueResolverFromFn($valueProvider);

        return $this;
    }

    public function ignore(bool $ignore = true): Options
    {
        $this->ignore = $ignore;

        return $this;
    }

    public function condition(callable $condition): Options
    {
        $this->conditionFn = $condition;

        return $this;
    }

    public function nullSubstitute(mixed $value): Options
    {
        $this->nullSubstitute = $value;

        return $this;
    }
}
