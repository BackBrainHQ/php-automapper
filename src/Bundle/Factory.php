<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Bundle;

use Backbrain\Automapper\AutoMapper;
use Backbrain\Automapper\Contract\AutoMapperInterface;
use Backbrain\Automapper\Contract\Builder\Config;
use Backbrain\Automapper\Contract\ProfileInterface;
use Backbrain\Automapper\MapperConfiguration;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

final class Factory implements LoggerAwareInterface
{
    private ?LoggerInterface $logger = null;
    private ?CacheItemPoolInterface $cacheItemPool = null;

    /**
     * @var ProfileInterface[]
     */
    private array $profiles = [];

    public function create(): AutoMapperInterface
    {
        $mapperConfig = new MapperConfiguration(function (Config $config) {
            foreach ($this->profiles as $profile) {
                $config->addProfile($profile);
            }
        });

        return new AutoMapper($mapperConfig, $this->cacheItemPool, $this->logger);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function setCacheItemPool(?CacheItemPoolInterface $cacheItemPool): void
    {
        $this->cacheItemPool = $cacheItemPool;
    }

    public function addProfile(ProfileInterface $profile): void
    {
        $this->profiles[] = $profile;
    }
}
