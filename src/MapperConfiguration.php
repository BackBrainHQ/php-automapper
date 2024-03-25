<?php

declare(strict_types=1);

namespace Backbrain\Automapper;

use Backbrain\Automapper\Builder\DefaultConfig;
use Backbrain\Automapper\Contract\AutoMapperInterface;
use Backbrain\Automapper\Contract\Builder\Config;
use Backbrain\Automapper\Contract\MapInterface;
use Backbrain\Automapper\Contract\MapperConfigurationInterface;

class MapperConfiguration implements MapperConfigurationInterface
{
    private DefaultConfig $builder;

    /**
     * @param callable(Config $cnf):void|null $configFn
     */
    public function __construct(?callable $configFn = null)
    {
        $this->builder = new DefaultConfig();
        if (null === $configFn) {
            return;
        }

        $configFn($this->builder);
    }

    public function createMapper(): AutoMapperInterface
    {
        return new AutoMapper($this);
    }

    /**
     * @return MapInterface[]
     */
    public function getMaps(): array
    {
        return $this->builder->getMaps();
    }
}
