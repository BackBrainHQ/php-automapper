<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Fixtures;

abstract class BaseLvl1Dest extends BaseLvl0Dest
{
    private ?string $baseLvl1DestValue = null;

    public function getBaseLvl1DestValue(): ?string
    {
        return $this->baseLvl1DestValue;
    }

    public function setBaseLvl1DestValue(?string $baseLvl1DestValue): static
    {
        $this->baseLvl1DestValue = $baseLvl1DestValue;

        return $this;
    }
}
