<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Fixtures;

abstract class BaseLvl1Src extends BaseLvl0Src
{
    private ?string $baseLvl1SrcValue = null;

    public function getBaseLvl1SrcValue(): ?string
    {
        return $this->baseLvl1SrcValue;
    }

    public function setBaseLvl1SrcValue(?string $baseLvl1SrcValue): static
    {
        $this->baseLvl1SrcValue = $baseLvl1SrcValue;

        return $this;
    }
}
