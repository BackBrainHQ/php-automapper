<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Exceptions;

class MapperException extends \LogicException
{
    /**
     * Might happen when no mapping is found for a source and destination type. Hint: Check if a mapping is defined for the source and destination type pairs.
     */
    public const MISSING_MAP = 1000;

    /**
     * Might happen when a destination class does not exist.
     */
    public const CLASS_NOT_FOUND = 1001;

    /**
     * Might happen when a type converter or factory is used and the value to convert or instantiate is not of the expected type.
     */
    public const UNEXPECTED_TYPE = 1002;

    /**
     * Might happen when a collection is to be written to but is not writeable. Hint: Check if the collection implements ArrayAccess.
     */
    public const COLLECTION_NOT_WRITEABLE = 1003;

    /**
     * Might happen when a circular dependency is detected in the mapping stack.
     */
    public const CIRCULAR_DEPENDENCY = 1004;

    /**
     * Might happen when an illegal type expression is used.
     */
    public const ILLEGAL_TYPE_EXPRESSION = 1005;

    /**
     * Might happen when instantiation of a type fails.
     */
    public const INSTANTIATION_FAILED = 1006;

    public static function newMissingMapException(string $sourceType, string $destinationType): self
    {
        return new self(sprintf('No mapping found for source type "%s" and destination type "%s"', $sourceType, $destinationType), self::MISSING_MAP);
    }

    public static function newMissingMapsException(string $srcType, string ...$destTypes): self
    {
        if (count($destTypes) > 1) {
            return new self(sprintf('No mapping found for source type "%s" to any of the destination types "%s"', $srcType, implode('", "', $destTypes)), self::MISSING_MAP);
        }

        return new self(sprintf('No mapping found for source type "%s" to destination type "%s"', $srcType, implode('", "', $destTypes)), self::MISSING_MAP);
    }

    public static function newDestinationClassNotFoundException(string $destinationType): self
    {
        return new self(sprintf('Class for destination type "%s" does not exist', $destinationType), self::CLASS_NOT_FOUND);
    }

    public static function newUnexpectedTypeException(string $expectedType, mixed $actualValue): self
    {
        return new self(sprintf('Type conversion or instantiation failed due to unexpected type of value. Expected "%s", got "%s"', $expectedType, get_debug_type($actualValue)), self::UNEXPECTED_TYPE);
    }

    public static function newCollectionNotWriteableException(string $className): self
    {
        return new self(sprintf('Collection of type "%s" is not writeable since it does not implement ArrayAccess', $className), self::COLLECTION_NOT_WRITEABLE);
    }

    /**
     * @param string[] $stack
     */
    public static function newCircularDependencyException(array $stack, string $sourceType, string $mappedBy): self
    {
        return new self(sprintf('Circular dependency detected in mapping stack: "%s". Source type "%s" mapped by: "%s"', implode(' -> ', $stack), $sourceType, $mappedBy), self::CIRCULAR_DEPENDENCY);
    }

    public static function newIllegalTypeException(string $type): self
    {
        return new self(sprintf('Illegal type expression "%s"', $type), self::ILLEGAL_TYPE_EXPRESSION);
    }

    public static function newInstantiationFailedException(string $type, string $sourceType = 'unknown'): self
    {
        return new self(sprintf('Instantiation of type "%s" failed. Consider creating a map (for source type "%s") and/or use a TypeFactory (see constructUsing())', $type, $sourceType), self::INSTANTIATION_FAILED);
    }
}
