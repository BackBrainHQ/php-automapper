<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Fixtures;

abstract class BaseLvl0Dest
{
    private ?string $baseLvl0DestValue = null;

    public function getBaseLvl0DestValue(): ?string
    {
        return $this->baseLvl0DestValue;
    }

    public function setBaseLvl0DestValue(?string $baseLvl0DestValue): static
    {
        $this->baseLvl0DestValue = $baseLvl0DestValue;

        return $this;
    }
}
