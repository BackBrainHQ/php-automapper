<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract;

interface MemberInterface
{
    public function getDestinationProperty(): string;

    public function getValueProvider(): ?ValueResolverInterface;

    public function isIgnored(): bool;

    /**
     * @return callable(mixed $source):bool|null
     */
    public function getCondition(): ?callable;

    public function getNullSubstitute(): mixed;
}
