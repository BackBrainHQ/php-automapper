<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Fixtures;

class ScalarSrc extends BaseLvl1Src
{
    public string $aString;

    public int $anInt;

    public float $aFloat;

    public bool $aBool;

    public array $anArray;

    public ?string $aNullString = null;

    public function __construct(string $aString = 'aString', int $anInt = 123456, float $aFloat = 0.123456, bool $aBool = true, array $anArray = [null, 1, '2', [3]])
    {
        $this->aString = $aString;
        $this->anInt = $anInt;
        $this->aFloat = $aFloat;
        $this->aBool = $aBool;
        $this->anArray = $anArray;
    }
}
