<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Converter\Naming;

use Backbrain\Automapper\Contract\NamingConventionInterface;
use Jawira\CaseConverter\Convert;

/**
 * Converts a string to Ada case.
 * Example: `hello_world` to `Hello_World`.
 */
class AdaCaseNamingConvention implements NamingConventionInterface
{
    public function translate(string $name): string
    {
        return (new Convert($name))->toAda();
    }
}
