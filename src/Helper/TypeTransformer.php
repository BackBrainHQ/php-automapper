<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Helper;

use Symfony\Component\TypeInfo\Type;

/**
 * @internal
 */
final class TypeTransformer
{
    public static function toTypeInfoType(mixed $type): Type
    {
        if ($type instanceof Type) {
            return $type;
        }

        if (class_exists(\Symfony\Component\PropertyInfo\Type::class) && $type instanceof \Symfony\Component\PropertyInfo\Type) {
            return self::fromPropertyInfoType($type);
        }

        if (is_array($type)) {
            $types = [];
            foreach ($type as $t) {
                $types[] = self::toTypeInfoType($t);
            }

            if (0 === count($types)) {
                return Type::mixed();
            }

            if (1 === count($types)) {
                return $types[0];
            }

            return Type::union(...$types);
        }

        // Fallback for unknown types or already transformed
        return Type::mixed();
    }

    /** @phpstan-ignore-next-line */
    private static function fromPropertyInfoType(\Symfony\Component\PropertyInfo\Type $type): Type
    {
        // @phpstan-ignore-next-line
        $builtinType = $type->getBuiltinType();
        // @phpstan-ignore-next-line
        $isNullable = $type->isNullable();

        $convertedType = match ($builtinType) {
            'int' => Type::int(),
            'float' => Type::float(),
            'string' => Type::string(),
            'bool' => Type::bool(),
            'resource' => Type::resource(),
            'object' => self::createObjectType($type),
            'array' => self::createCollectionType($type),
            'iterable' => self::createIterableType($type),
            'callable' => Type::callable(),
            'null' => Type::null(),
            default => Type::mixed(),
        };

        /* @phpstan-ignore-next-line */
        if ($isNullable && !$convertedType instanceof Type\NullableType && !$convertedType instanceof Type\NullType) {
            return Type::nullable($convertedType);
        }

        return $convertedType;
    }

    /** @phpstan-ignore-next-line */
    private static function createObjectType(\Symfony\Component\PropertyInfo\Type $type): Type
    {
        // @phpstan-ignore-next-line
        $className = $type->getClassName();
        if (null === $className) {
            return Type::object();
        }

        // @phpstan-ignore-next-line
        if ($type->isCollection()) {
            // @phpstan-ignore-next-line
            return self::createCollectionType($type, Type::object($className));
        }

        // @phpstan-ignore-next-line
        return Type::object($className);
    }

    /** @phpstan-ignore-next-line */
    private static function createCollectionType(\Symfony\Component\PropertyInfo\Type $type, ?Type $baseType = null): Type
    {
        // @phpstan-ignore-next-line
        $keyTypes = $type->getCollectionKeyTypes();
        // @phpstan-ignore-next-line
        $valueTypes = $type->getCollectionValueTypes();

        $keyType = self::toTypeInfoType($keyTypes);
        $valueType = self::toTypeInfoType($valueTypes);

        if (null === $baseType) {
            // @phpstan-ignore-next-line
            if ('array' === $type->getBuiltinType()) {
                // @phpstan-ignore-next-line
                return Type::array($valueType, $keyType);
            }

            // @phpstan-ignore-next-line
            return Type::iterable($valueType, $keyType);
        }

        // @phpstan-ignore-next-line
        return Type::collection($baseType, $valueType, $keyType);
    }

    /** @phpstan-ignore-next-line */
    private static function createIterableType(\Symfony\Component\PropertyInfo\Type $type): Type
    {
        // @phpstan-ignore-next-line
        if ($type->isCollection()) {
            return self::createCollectionType($type);
        }

        return Type::iterable();
    }
}
