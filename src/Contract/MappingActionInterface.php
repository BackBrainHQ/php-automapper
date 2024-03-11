<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract;

interface MappingActionInterface
{
    public function process(mixed $source, mixed $destination, ResolutionContextInterface $context): void;
}
