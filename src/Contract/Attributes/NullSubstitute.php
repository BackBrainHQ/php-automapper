<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class NullSubstitute
{
    private mixed $nullSubstitute;

    public function __construct(mixed $nullSubstitute = null)
    {
        $this->nullSubstitute = $nullSubstitute;
    }

    public function getNullSubstitute(): mixed
    {
        return $this->nullSubstitute;
    }
}
