<?php

namespace Backbrain\Automapper\Tests\Fixtures;

class ArrayDest
{
    /**
     * @var mixed[]
     */
    public array $anArrayOfMixed;

    public array $anUntypedArray;

    /**
     * @var array<int|string>
     */
    public array $anArrayOfStringInt;

    /**
     * @var ScalarDestSnakeCase[]
     */
    public array $anArrayOfScalarDestSnakeCase;

    /**
     * @var ScalarDestInterface[]
     */
    public array $anArrayOfScalarDestInterface;
}
