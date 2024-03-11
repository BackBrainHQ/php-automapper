<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract;

interface TypeFactoryInterface
{
    public function create(mixed $source, ResolutionContextInterface $context): object;
}
