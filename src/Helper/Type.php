<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Helper;

/**
 * @internal This class is not part of the public API and may change at any time!
 */
class Type
{
    public const INT = 'int';
    public const FLOAT = 'float';
    public const STRING = 'string';
    public const BOOL = 'bool';
    public const OBJECT = 'object';
    public const ARRAY = 'array';
    public const CALLABLE = 'callable';
    public const ITERABLE = 'iterable';

    public static function toString(mixed $type): string
    {
        if (class_exists(\Symfony\Component\PropertyInfo\Type::class) && $type instanceof \Symfony\Component\PropertyInfo\Type) {
            $baseType = $type->getClassName() ?? $type->getBuiltinType();
            /** @var string[] $valueTypes */
            $valueTypes = array_map(function (mixed $type) {
                return self::toString($type);
            }, (array) $type->getCollectionValueTypes());
            /** @var string[] $keyTypes */
            $keyTypes = array_map(function (mixed $type) {
                return self::toString($type);
            }, (array) $type->getCollectionKeyTypes());

            if ($type->isCollection()) {
                if (count($keyTypes) > 0) {
                    /* @phpstan-ignore-next-line */
                    return sprintf('%s<%s, %s>', (string) $baseType, implode('|', $keyTypes), implode('|', $valueTypes));
                }

                if (0 === count($valueTypes)) {
                    return self::arrayOf('mixed');
                }

                return self::arrayOf(...$valueTypes);
            }

            /* @phpstan-ignore-next-line */
            return (string) $baseType;
        }

        if ($type instanceof \Symfony\Component\TypeInfo\Type) {
            $str = (string) $type->__toString();
            // Normalize array<int|string, V> to array<V> to match PropertyInfo behavior
            $str = str_replace('<int|string, ', '<', $str);
            $str = str_replace('<int|string,', '<', $str);
            // Normalize array<int, V> to array<V>
            $str = str_replace('<int, ', '<', $str);
            $str = str_replace('<int,', '<', $str);
            // Normalize list<V> to array<V>
            if (str_starts_with($str, 'list<')) {
                $str = 'array'.substr($str, 4);
            }

            return $str;
        }

        return ltrim(get_debug_type($type), '\\');
    }

    public static function arrayOf(string ...$type): string
    {
        return sprintf('array<%s>', implode('|', $type));
    }

    public static function getBuiltinType(mixed $type): string
    {
        if (class_exists(\Symfony\Component\PropertyInfo\Type::class) && $type instanceof \Symfony\Component\PropertyInfo\Type) {
            /* @phpstan-ignore-next-line */
            return (string) $type->getBuiltinType();
        }

        if ($type instanceof \Symfony\Component\TypeInfo\Type\GenericType) {
            return self::getBuiltinType(self::getWrappedType($type));
        }
        if ($type instanceof \Symfony\Component\TypeInfo\Type\BuiltinType) {
            return $type->getTypeIdentifier()->value;
        }
        if ($type instanceof \Symfony\Component\TypeInfo\Type\ObjectType) {
            return 'object';
        }
        if ($type instanceof \Symfony\Component\TypeInfo\Type\CollectionType) {
            return self::getBuiltinType(self::getWrappedType($type));
        }
        /* @phpstan-ignore-next-line */
        if ($type instanceof \Symfony\Component\TypeInfo\Type\NullType) {
            return 'null';
        }
        if ($type instanceof \Symfony\Component\TypeInfo\Type\UnionType) {
            // If union contains null, it is nullable. But what is the builtin type?
            // We probably shouldn't call getBuiltinType on UnionType.
            // But AutoMapper might.
            return 'mixed';
        }

        return 'mixed';
    }

    public static function getClassName(mixed $type): ?string
    {
        if (class_exists(\Symfony\Component\PropertyInfo\Type::class) && $type instanceof \Symfony\Component\PropertyInfo\Type) {
            /* @phpstan-ignore-next-line */
            return $type->getClassName();
        }

        if ($type instanceof \Symfony\Component\TypeInfo\Type\GenericType) {
            return self::getClassName(self::getWrappedType($type));
        }
        if ($type instanceof \Symfony\Component\TypeInfo\Type\CollectionType) {
            return self::getClassName(self::getWrappedType($type));
        }
        if ($type instanceof \Symfony\Component\TypeInfo\Type\ObjectType) {
            return $type->getClassName();
        }

        return null;
    }

    public static function isCollection(mixed $type): bool
    {
        if (class_exists(\Symfony\Component\PropertyInfo\Type::class) && $type instanceof \Symfony\Component\PropertyInfo\Type) {
            return (bool) $type->isCollection();
        }

        if ($type instanceof \Symfony\Component\TypeInfo\Type\GenericType) {
            return self::isCollection(self::getWrappedType($type));
        }

        if ($type instanceof \Symfony\Component\TypeInfo\Type\CollectionType) {
            return true;
        }

        if ($type instanceof \Symfony\Component\TypeInfo\Type\ObjectType) {
            $class = $type->getClassName();
            if (is_subclass_of($class, \Traversable::class) || \Traversable::class === $class) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<mixed>
     */
    public static function getCollectionKeyTypes(mixed $type): array
    {
        if (class_exists(\Symfony\Component\PropertyInfo\Type::class) && $type instanceof \Symfony\Component\PropertyInfo\Type) {
            /* @phpstan-ignore-next-line */
            return (array) $type->getCollectionKeyTypes();
        }

        if ($type instanceof \Symfony\Component\TypeInfo\Type\GenericType) {
            $wrapped = self::getWrappedType($type);
            if ($wrapped instanceof \Symfony\Component\TypeInfo\Type\ObjectType) {
                $vars = $type->getVariableTypes();
                if (count($vars) >= 2) {
                    return [$vars[0]];
                }
            }

            return self::getCollectionKeyTypes($wrapped);
        }

        if ($type instanceof \Symfony\Component\TypeInfo\Type\CollectionType) {
            return [$type->getCollectionKeyType()];
        }

        return [];
    }

    /**
     * @return array<mixed>
     */
    public static function getCollectionValueTypes(mixed $type): array
    {
        if (class_exists(\Symfony\Component\PropertyInfo\Type::class) && $type instanceof \Symfony\Component\PropertyInfo\Type) {
            /* @phpstan-ignore-next-line */
            return (array) $type->getCollectionValueTypes();
        }

        if ($type instanceof \Symfony\Component\TypeInfo\Type\GenericType) {
            $wrapped = self::getWrappedType($type);
            if ($wrapped instanceof \Symfony\Component\TypeInfo\Type\ObjectType) {
                $vars = $type->getVariableTypes();
                if (count($vars) >= 2) {
                    return [$vars[1]];
                }
                if (1 === count($vars)) {
                    return [$vars[0]];
                }
            }

            return self::getCollectionValueTypes($wrapped);
        }

        if ($type instanceof \Symfony\Component\TypeInfo\Type\CollectionType) {
            return [$type->getCollectionValueType()];
        }

        return [];
    }

    public static function isNullable(mixed $type): bool
    {
        if (class_exists(\Symfony\Component\PropertyInfo\Type::class) && $type instanceof \Symfony\Component\PropertyInfo\Type) {
            return (bool) $type->isNullable();
        }

        return $type instanceof \Symfony\Component\TypeInfo\Type && $type->isNullable();
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

    /**
     * @param \Symfony\Component\TypeInfo\Type\GenericType|\Symfony\Component\TypeInfo\Type\CollectionType $type
     *
     * @phpstan-ignore-next-line
     */
    private static function getWrappedType(mixed $type): mixed
    {
        if (method_exists($type, 'getWrappedType')) {
            return $type->getWrappedType();
        }

        /* @phpstan-ignore-next-line */
        return $type->getType();
    }
}
