<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract\Builder;

use Backbrain\Automapper\Contract\ProfileInterface;

interface ProfileBuilderInterface
{
    /**
     * Initializes and configures the mapping between two types.
     *
     * @param string $sourceType      the fully qualified class name (or built-in type) of the source type
     * @param string $destinationType the fully qualified class name (or built-in type) of the destination type
     */
    public function createMap(string $sourceType, string $destinationType): MapBuilderInterface;

    public function addProfile(ProfileInterface $profile): ProfileBuilderInterface;
}
