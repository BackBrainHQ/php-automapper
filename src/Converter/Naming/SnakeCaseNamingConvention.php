<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Converter\Naming;

use Backbrain\Automapper\Contract\NamingConventionInterface;
use Jawira\CaseConverter\Convert;

/**
 * Converts a string to snake case.
 * Example: `helloWorld` to `hello_world`.
 */
class SnakeCaseNamingConvention implements NamingConventionInterface
{
    public function translate(string $name): string
    {
        return (new Convert($name))->toSnake();
    }
}
