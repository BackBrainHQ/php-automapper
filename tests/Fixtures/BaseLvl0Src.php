<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Fixtures;

abstract class BaseLvl0Src
{
    private ?string $baseLvl0SrcValue = null;

    public function getBaseLvl0SrcValue(): ?string
    {
        return $this->baseLvl0SrcValue;
    }

    public function setBaseLvl0SrcValue(?string $baseLvl0SrcValue): static
    {
        $this->baseLvl0SrcValue = $baseLvl0SrcValue;

        return $this;
    }
}
