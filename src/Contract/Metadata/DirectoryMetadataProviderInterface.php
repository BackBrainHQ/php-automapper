<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract\Metadata;

interface DirectoryMetadataProviderInterface
{
    /**
     * This method is used to scan the given path recursively for PHP classes and return their fully qualified class names.
     *
     * @return class-string[]
     */
    public function scanPath(string ...$path): array;
}
