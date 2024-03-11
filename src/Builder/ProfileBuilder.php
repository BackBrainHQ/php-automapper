<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Builder;

use Backbrain\Automapper\Contract\Builder\MapBuilderInterface;
use Backbrain\Automapper\Contract\Builder\ProfileBuilderInterface;
use Backbrain\Automapper\Contract\MapInterface;
use Backbrain\Automapper\Contract\ProfileInterface;

class ProfileBuilder implements ProfileBuilderInterface
{
    /**
     * @var ProfileInterface[]
     */
    private array $profiles = [];

    /**
     * @var MapBuilder[]
     */
    private array $mapBuilders = [];

    public function createMap(string $sourceType, string $destinationType): MapBuilderInterface
    {
        $builder = new MapBuilder($this, $sourceType, $destinationType);
        $this->mapBuilders[] = $builder;

        return $builder;
    }

    public function addProfile(ProfileInterface $profile): ProfileBuilderInterface
    {
        $this->profiles[] = $profile;

        return $this;
    }

    /**
     * @return MapInterface[]
     */
    public function getMaps(): array
    {
        // for any duplicate map definitions, having matching source and destination type
        // we only keep the last one. To be able to overwrite a map.
        $deduplication = [];
        foreach ($this->profiles as $profile) {
            foreach ($profile->getMaps() as $map) {
                $deduplication[sprintf('%s$%s', $map->getSourceType(), $map->getDestinationType())] = $map;
            }
        }

        foreach ($this->mapBuilders as $builder) {
            $map = $builder->build();
            $deduplication[sprintf('%s$%s', $map->getSourceType(), $map->getDestinationType())] = $map;
        }

        return array_values($deduplication);
    }
}
