<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Helper;

/**
 * @internal This class is not part of the public API and may change at any time!
 */
readonly class Value
{
    public static function asInt(mixed $value): int
    {
        if (!is_int($value)) {
            throw new \InvalidArgumentException(sprintf('Value must be an integer, got "%s"', get_debug_type($value)));
        }

        return $value;
    }

    public static function asFloat(mixed $value): float
    {
        if (!is_float($value)) {
            throw new \InvalidArgumentException(sprintf('Value must be a float, got "%s"', get_debug_type($value)));
        }

        return $value;
    }

    public static function asString(mixed $value): string
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException(sprintf('Value must be a string, got "%s"', get_debug_type($value)));
        }

        return $value;
    }

    public static function asBool(mixed $value): bool
    {
        if (!is_bool($value)) {
            throw new \InvalidArgumentException(sprintf(sprintf('Value must be a boolean, got "%s"', get_debug_type($value))));
        }

        return $value;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public static function asObject(mixed $value, string $class): object
    {
        if (!is_object($value)) {
            throw new \InvalidArgumentException(sprintf('Value must be an object, got "%s"', get_debug_type($value)));
        }

        if (!is_a($value, $class)) {
            throw new \InvalidArgumentException(sprintf('Value must be an instance of "%s", got "%s"', $class, get_debug_type($value)));
        }

        return $value;
    }
}
