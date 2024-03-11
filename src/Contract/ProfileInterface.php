<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract;

interface ProfileInterface
{
    /**
     * @return MapInterface[]
     */
    public function getMaps(): array;
}
