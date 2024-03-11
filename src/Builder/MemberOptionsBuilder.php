<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Builder;

use Backbrain\Automapper\Contract\Builder\MemberOptionsBuilderInterface;
use Backbrain\Automapper\Contract\MemberInterface;
use Backbrain\Automapper\Contract\ValueResolverInterface;
use Backbrain\Automapper\Model\Member;

class MemberOptionsBuilder implements MemberOptionsBuilderInterface
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

    public function mapFrom(ValueResolverInterface|callable $valueProvider): MemberOptionsBuilderInterface
    {
        if ($valueProvider instanceof ValueResolverInterface) {
            $this->valueProvider = $valueProvider;

            return $this;
        }

        $valueProvider = new class($valueProvider) implements ValueResolverInterface {
            /**
             * @var callable(object):mixed
             */
            private mixed $valueProvider;

            public function __construct(callable $valueProvider)
            {
                $this->valueProvider = $valueProvider;
            }

            public function resolve(object $source): mixed
            {
                return ($this->valueProvider)($source);
            }
        };

        $this->valueProvider = $valueProvider;

        return $this;
    }

    public function ignore(bool $ignore = true): MemberOptionsBuilderInterface
    {
        $this->ignore = $ignore;

        return $this;
    }

    public function condition(callable $condition): MemberOptionsBuilderInterface
    {
        $this->conditionFn = $condition;

        return $this;
    }

    public function nullSubstitute(mixed $value): MemberOptionsBuilderInterface
    {
        $this->nullSubstitute = $value;

        return $this;
    }
}
