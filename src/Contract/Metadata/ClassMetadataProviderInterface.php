<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract\Metadata;

use Backbrain\Automapper\Contract\ProfileInterface;

interface ClassMetadataProviderInterface
{
    /**
     * @param class-string $className
     *
     * @return ProfileInterface[]
     */
    public function getProfiles(string $className): array;
}
