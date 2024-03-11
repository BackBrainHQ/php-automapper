<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Converter\Naming;

use Backbrain\Automapper\Contract\NamingConventionInterface;
use Jawira\CaseConverter\Convert;

/**
 * Converts a string to camel case.
 * Example: `hello_world` to `helloWorld`.
 */
class CamelCaseNamingConvention implements NamingConventionInterface
{
    public function translate(string $name): string
    {
        return (new Convert($name))->toCamel();
    }
}
