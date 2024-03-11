<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract;

interface MapperConfigurationInterface
{
    public function createMapper(): AutoMapperInterface;

    /**
     * @return MapInterface[]
     */
    public function getMaps(): array;
}
