<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Builder;

use Backbrain\Automapper\Contract\Builder\Config;
use Backbrain\Automapper\Contract\Builder\Map;
use Backbrain\Automapper\Contract\MapInterface;
use Backbrain\Automapper\Contract\ProfileInterface;

/**
 * @internal This class is not part of the public API and may change at any time!
 */
class DefaultConfig implements Config
{
    /**
     * @var ProfileInterface[]
     */
    private array $profiles = [];

    /**
     * @var DefaultMap[]
     */
    private array $mapBuilders = [];

    public function createMap(string $sourceType, string $destinationType): Map
    {
        $builder = new DefaultMap($this, $sourceType, $destinationType);
        $this->mapBuilders[] = $builder;

        return $builder;
    }

    public function addProfile(ProfileInterface $profile): Config
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
