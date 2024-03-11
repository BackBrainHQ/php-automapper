<?php

namespace Backbrain\Automapper\Tests\Fixtures;

class ArraySrc
{
    public function __construct(
        public array $srcArrayOfStrings = ['aString', 'bString', 'cString'],
        public array $srcArrayOfStringInt = [1, '2', 3],
        public array $srcArrayOfScalarSrc = []
    ) {
    }
}
