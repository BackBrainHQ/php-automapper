<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Converter\Naming;

use Backbrain\Automapper\Contract\NamingConventionInterface;
use Jawira\CaseConverter\Convert;

/**
 * Converts a string to macro case.
 * Example: `hello_world` to `HELLO_WORLD`.
 */
class MacroCaseNamingConvention implements NamingConventionInterface
{
    public function translate(string $name): string
    {
        return (new Convert($name))->toMacro();
    }
}
