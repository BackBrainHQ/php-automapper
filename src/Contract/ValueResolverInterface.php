<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract;

interface ValueResolverInterface
{
    public function resolve(object $source): mixed;
}
