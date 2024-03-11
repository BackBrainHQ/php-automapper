<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Helper;

use Backbrain\Automapper\Contract\MappingActionInterface;
use Backbrain\Automapper\Contract\ResolutionContextInterface;
use Backbrain\Automapper\Contract\TypeConverterInterface;
use Backbrain\Automapper\Contract\TypeFactoryInterface;

class Func
{
    /**
     * @param TypeFactoryInterface|callable(mixed $source, ResolutionContextInterface $context):object $factory
     */
    public static function typeFactoryFromFn(TypeFactoryInterface|callable $factory): TypeFactoryInterface
    {
        if ($factory instanceof TypeFactoryInterface) {
            return $factory;
        }

        return new class($factory) implements TypeFactoryInterface {
            /**
             * @var callable(mixed, ResolutionContextInterface):object
             */
            private mixed $factory;

            public function __construct(callable $factory)
            {
                $this->factory = $factory;
            }

            public function create(mixed $source, ResolutionContextInterface $context): object
            {
                return ($this->factory)($source, $context);
            }
        };
    }

    /**
     * @param TypeConverterInterface|callable(mixed $source, ResolutionContextInterface $context):mixed $converter
     */
    public static function typeConverterFromFn(TypeConverterInterface|callable $converter): TypeConverterInterface
    {
        if ($converter instanceof TypeConverterInterface) {
            return $converter;
        }

        return new class($converter) implements TypeConverterInterface {
            /**
             * @var callable(mixed, ResolutionContextInterface):mixed
             */
            private mixed $converter;

            public function __construct(callable $converter)
            {
                $this->converter = $converter;
            }

            public function convert(mixed $source, ResolutionContextInterface $context): mixed
            {
                return ($this->converter)($source, $context);
            }
        };
    }

    /**
     * @param MappingActionInterface|callable(mixed $source, mixed $destination, ResolutionContextInterface $context):void $action
     */
    public static function mappingActionFromFn(callable|MappingActionInterface $action): MappingActionInterface
    {
        if ($action instanceof MappingActionInterface) {
            return $action;
        }

        return new class($action) implements MappingActionInterface {
            /**
             * @var callable(mixed, mixed, ResolutionContextInterface):void
             */
            private mixed $action;

            public function __construct(callable $action)
            {
                $this->action = $action;
            }

            public function process(mixed $source, mixed $destination, ResolutionContextInterface $context): void
            {
                ($this->action)($source, $destination, $context);
            }
        };
    }
}
