<?php

namespace Backbrain\Automapper\Tests\Fixtures;

class ObjectDestSameType
{
    private ?ScalarSrc $obj = null;

    public function getObj(): ?ScalarSrc
    {
        return $this->obj;
    }

    public function setObj(?ScalarSrc $obj): ObjectDestSameType
    {
        $this->obj = $obj;

        return $this;
    }
}
