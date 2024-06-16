<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract\Metadata;

interface AttributeMetadataProviderInterface
{
    /**
     * @param class-string<object> $class
     * @param class-string[]       $filter
     *
     * @return object[]
     *
     * @throws \ReflectionException
     */
    public function getPropertyAttributes(string $class, string $property, array $filter = []): array;

    /**
     * Get a single attribute of a class.
     *
     * @template T of object
     *
     * @param class-string<object>|\ReflectionClass<object> $target
     * @param class-string<T>                               $attrClassName
     *
     * @return T|null
     *
     * @throws \ReflectionException
     */
    public function getClassAttr(string|\ReflectionClass $target, string $attrClassName): ?object;

    /**
     * Get all attributes of a class.
     *
     * @template T of object
     *
     * @param class-string<object>|\ReflectionClass<object> $target
     * @param class-string<T>                               $attrClassName
     *
     * @return array<T>
     *
     * @throws \ReflectionException
     */
    public function getClassAttrs(string|\ReflectionClass $target, string $attrClassName): array;
}
