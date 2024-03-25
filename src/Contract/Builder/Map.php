<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract\Builder;

use Backbrain\Automapper\Contract\MappingActionInterface;
use Backbrain\Automapper\Contract\NamingConventionInterface;
use Backbrain\Automapper\Contract\ResolutionContextInterface;
use Backbrain\Automapper\Contract\TypeConverterInterface;
use Backbrain\Automapper\Contract\TypeFactoryInterface;

interface Map extends Config
{
    /**
     * Configures a mapping between a source member and a destination member.
     *
     * @param callable(Options):void $optFn
     */
    public function forMember(string $destinationProperty, callable $optFn): static;

    /**
     * If you need full control over the mapping process, you can use this method to provide a custom type converter.
     *
     * @param TypeConverterInterface|callable(mixed $source, ResolutionContextInterface $context):mixed $converter
     */
    public function convertUsing(TypeConverterInterface|callable $converter): static;

    /**
     * Destination type factory.
     *
     * @param TypeFactoryInterface|callable(mixed $source, ResolutionContextInterface $context):object $factory
     */
    public function constructUsing(TypeFactoryInterface|callable $factory): static;

    /**
     * Will use mapping configuration from given destination type map.
     *
     * @param class-string $destinationType
     */
    public function as(string $destinationType): static;

    /**
     * Naming convention for source members.
     *
     * @param class-string<NamingConventionInterface>|NamingConventionInterface $namingConvention
     */
    public function sourceMemberNamingConvention(string|NamingConventionInterface $namingConvention): static;

    /**
     * Naming convention for destination members.
     *
     * @param class-string<NamingConventionInterface>|NamingConventionInterface $namingConvention
     */
    public function destinationMemberNamingConvention(string|NamingConventionInterface $namingConvention): static;

    /**
     * Action to be executed before mapping.
     *
     * @param MappingActionInterface|callable(mixed $source, mixed $destination, ResolutionContextInterface $context):void $action
     */
    public function beforeMap(MappingActionInterface|callable $action): static;

    /**
     * Action to be executed after mapping.
     *
     * @param MappingActionInterface|callable(mixed $source, mixed $destination, ResolutionContextInterface $context):void $action
     */
    public function afterMap(MappingActionInterface|callable $action): static;

    /**
     * Inherit mapping configuration down to another map.
     *
     * @param string $sourceType      the fully qualified class name (or built-in type) of the source type
     * @param string $destinationType the fully qualified class name (or built-in type) of the destination type
     */
    public function include(string $sourceType, string $destinationType): static;

    /**
     * Inherit mapping configuration from another map.
     *
     * @param string $sourceType      the fully qualified class name (or built-in type) of the source type
     * @param string $destinationType the fully qualified class name (or built-in type) of the destination type
     */
    public function includeBase(string $sourceType, string $destinationType): static;
}
