<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Helper;

/**
 * @internal This class is not part of the public API and may change at any time!
 */
class Type
{
    public const INT = \Symfony\Component\PropertyInfo\Type::BUILTIN_TYPE_INT;
    public const FLOAT = \Symfony\Component\PropertyInfo\Type::BUILTIN_TYPE_FLOAT;
    public const STRING = \Symfony\Component\PropertyInfo\Type::BUILTIN_TYPE_STRING;
    public const BOOL = \Symfony\Component\PropertyInfo\Type::BUILTIN_TYPE_BOOL;
    public const OBJECT = \Symfony\Component\PropertyInfo\Type::BUILTIN_TYPE_OBJECT;
    public const ARRAY = \Symfony\Component\PropertyInfo\Type::BUILTIN_TYPE_ARRAY;
    public const CALLABLE = \Symfony\Component\PropertyInfo\Type::BUILTIN_TYPE_CALLABLE;
    public const ITERABLE = \Symfony\Component\PropertyInfo\Type::BUILTIN_TYPE_ITERABLE;

    public static function toString(mixed $type): string
    {
        if ($type instanceof \Symfony\Component\PropertyInfo\Type) {
            $baseType = $type->getClassName() ?? $type->getBuiltinType();
            $valueTypes = array_map(function (\Symfony\Component\PropertyInfo\Type $type) {
                return self::toString($type);
            }, $type->getCollectionValueTypes());
            $keyTypes = array_map(function (\Symfony\Component\PropertyInfo\Type $type) {
                return self::toString($type);
            }, $type->getCollectionKeyTypes());

            if ($type->isCollection()) {
                if (count($keyTypes) > 0) {
                    return sprintf('%s<%s, %s>', $baseType, implode('|', $keyTypes), implode('|', $valueTypes));
                }

                return self::arrayOf(...$valueTypes);
            }

            return $baseType;
        }

        return ltrim(get_debug_type($type), '\\');
    }

    public static function arrayOf(string ...$type): string
    {
        return sprintf('array<%s>', implode('|', $type));
    }

    /**
     * @param string[] $keyTypes
     * @param string[] $valueTypes
     */
    public static function mapOf(array $keyTypes, array $valueTypes): string
    {
        return sprintf('array<%s,%s>', implode('|', $keyTypes), implode('|', $valueTypes));
    }

    /**
     * @param class-string<\ArrayAccess<mixed,mixed>> $type
     * @param string[]                                $keyTypes
     * @param string[]                                $valueTypes
     */
    public static function collectionOf(string $type, array $keyTypes, array $valueTypes): string
    {
        return sprintf('%s<%s,%s>', $type, implode('|', $keyTypes), implode('|', $valueTypes));
    }
}
