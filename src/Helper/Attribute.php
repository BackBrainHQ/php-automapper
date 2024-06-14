<?php

namespace Backbrain\Automapper\Helper;

class Attribute
{
    /**
     * @param class-string<object> $class
     * @param class-string[]       $filter
     *
     * @return object[]
     */
    public static function getPropertyAttributes(string $class, string $property, array $filter = []): array
    {
        $reflection = new \ReflectionProperty($class, $property);

        $result = array_map(function (\ReflectionAttribute $attr) {
            return $attr->newInstance();
        }, $reflection->getAttributes());

        if (count($filter) > 0) {
            $result = array_filter($result, function (object $attr) use ($filter) {
                return in_array($attr::class, $filter);
            });
        }

        return $result;
    }

    /**
     * Get a single attribute of a class.
     *
     * @template T of object
     *
     * @param class-string<object>|\ReflectionClass<object> $target
     * @param class-string<T>                               $attrClassName
     *
     * @return T|null
     */
    public static function getClassAttr(string|\ReflectionClass $target, string $attrClassName): ?object
    {
        $attrs = self::getClassAttrs($target, $attrClassName);
        if (0 === count($attrs)) {
            return null;
        }

        if (count($attrs) > 1) {
            throw new \InvalidArgumentException(sprintf('Multiple attributes of type "%s" found', $attrClassName));
        }

        return $attrs[0];
    }

    /**
     * Get all attributes of a class.
     *
     * @template T of object
     *
     * @param class-string<object>|\ReflectionClass<object> $target
     * @param class-string<T>                               $attrClassName
     *
     * @return array<T>
     */
    public static function getClassAttrs(string|\ReflectionClass $target, string $attrClassName): array
    {
        if (is_string($target)) {
            if (!class_exists($target)) {
                throw new \InvalidArgumentException(sprintf('Target class "%s" does not exist', $target));
            }

            $target = new \ReflectionClass($target);
        }

        if (!class_exists($attrClassName)) {
            throw new \InvalidArgumentException(sprintf('Attribute class %s does not exist', $attrClassName));
        }

        $mapAttrs = $target->getAttributes($attrClassName);

        return array_map(function (\ReflectionAttribute $attr) {
            return $attr->newInstance();
        }, $mapAttrs);
    }
}
