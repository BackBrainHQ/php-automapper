<?php

namespace Backbrain\Automapper;

use Backbrain\Automapper\Builder\ProfileBuilder;
use Backbrain\Automapper\Contract\AutoMapperInterface;
use Backbrain\Automapper\Contract\Builder\ProfileBuilderInterface;
use Backbrain\Automapper\Contract\MapInterface;
use Backbrain\Automapper\Contract\MapperConfigurationInterface;

class MapperConfiguration implements MapperConfigurationInterface
{
    private ProfileBuilder $builder;

    /**
     * @param callable(ProfileBuilderInterface $cnf):void|null $configFn
     */
    public function __construct(?callable $configFn = null)
    {
        $this->builder = new ProfileBuilder();
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
