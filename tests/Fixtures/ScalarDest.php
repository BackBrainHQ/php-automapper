<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Fixtures;

class ScalarDest extends BaseLvl1Dest implements ScalarDestInterface
{
    public string $aString = '';

    public int $anInt = 0;

    public float $aFloat = 0.0;

    public bool $aBool = false;

    public array $anArray = [];

    public ?string $aNullString = null;

    public ?string $unmapped = null;

    public function getAString(): string
    {
        return $this->aString;
    }

    public function getAnInt(): int
    {
        return $this->anInt;
    }

    public function getAFloat(): float
    {
        return $this->aFloat;
    }

    public function isABool(): bool
    {
        return $this->aBool;
    }

    public function getAnArray(): array
    {
        return $this->anArray;
    }

    public function getUnmapped(): ?string
    {
        return $this->unmapped;
    }

    public function setUnmapped(?string $unmapped): static
    {
        $this->unmapped = $unmapped;

        return $this;
    }
}
