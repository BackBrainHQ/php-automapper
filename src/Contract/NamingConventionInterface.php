<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract;

interface NamingConventionInterface
{
    public function translate(string $name): string;
}
