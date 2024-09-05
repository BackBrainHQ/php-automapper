<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Model;

use Backbrain\Automapper\Contract\MemberInterface;
use Backbrain\Automapper\Contract\ValueResolverInterface;

class Member implements MemberInterface
{
    /**
     * @var callable(mixed):bool|null
     */
    private mixed $conditionFn;

    /**
     * @param callable(mixed):bool|null $condition
     */
    public function __construct(
        private readonly string $destinationPropertyPath,
        private readonly ?ValueResolverInterface $valueProvider = null,
        private readonly bool $ignore = false,
        private readonly mixed $nullSubstitute = null,
        ?callable $condition = null,
    ) {
        $this->conditionFn = $condition;
    }

    public function getDestinationProperty(): string
    {
        return $this->destinationPropertyPath;
    }

    public function getValueProvider(): ?ValueResolverInterface
    {
        return $this->valueProvider;
    }

    public function isIgnored(): bool
    {
        return $this->ignore;
    }

    public function getCondition(): ?callable
    {
        return $this->conditionFn;
    }

    public function getNullSubstitute(): mixed
    {
        return $this->nullSubstitute;
    }
}
