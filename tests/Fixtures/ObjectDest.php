<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Fixtures;

class ObjectDest
{
    private ?ScalarDestSnakeCase $obj = null;

    public function getObj(): ?ScalarDestSnakeCase
    {
        return $this->obj;
    }

    public function setObj(?ScalarDestSnakeCase $obj): ObjectDest
    {
        $this->obj = $obj;

        return $this;
    }
}
