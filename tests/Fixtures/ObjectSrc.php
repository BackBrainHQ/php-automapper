<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Fixtures;

class ObjectSrc
{
    private ScalarSrc $obj;

    public function __construct(ScalarSrc $obj)
    {
        $this->obj = $obj;
    }

    public function getObj(): ScalarSrc
    {
        return $this->obj;
    }
}
