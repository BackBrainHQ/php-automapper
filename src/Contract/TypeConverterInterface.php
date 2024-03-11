<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract;

interface TypeConverterInterface
{
    public function convert(mixed $source, ResolutionContextInterface $context): mixed;
}
