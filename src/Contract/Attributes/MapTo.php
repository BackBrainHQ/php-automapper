<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract\Attributes;

use Backbrain\Automapper\Contract\MappingActionInterface;
use Backbrain\Automapper\Contract\TypeConverterInterface;
use Backbrain\Automapper\Contract\TypeFactoryInterface;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class MapTo
{
    private string $dest;

    /**
     * @var MappingActionInterface|class-string<MappingActionInterface>|null
     */
    private MappingActionInterface|string|null $beforeMap;

    /**
     * @var MappingActionInterface|class-string<MappingActionInterface>|null
     */
    private MappingActionInterface|string|null $afterMap;

    /**
     * @var TypeConverterInterface|class-string<TypeConverterInterface>|null
     */
    private TypeConverterInterface|string|null $convertUsing;

    /**
     * @var TypeFactoryInterface|class-string<TypeFactoryInterface>|null
     */
    private TypeFactoryInterface|string|null $constructUsing;

    /**
     * @param string                                                           $dest           The destination type name
     * @param TypeFactoryInterface|class-string<TypeFactoryInterface>|null     $constructUsing Specifies a factory to use to create the destination object
     * @param TypeConverterInterface|class-string<TypeConverterInterface>|null $convertUsing   Specifies a converter to use to convert the source object
     * @param MappingActionInterface|class-string<MappingActionInterface>|null $beforeMap      Run this action before mapping
     * @param MappingActionInterface|class-string<MappingActionInterface>|null $afterMap       Run this action after mapping
     */
    public function __construct(string $dest, TypeFactoryInterface|string|null $constructUsing = null, TypeConverterInterface|string|null $convertUsing = null, MappingActionInterface|string|null $beforeMap = null, MappingActionInterface|string|null $afterMap = null)
    {
        $this->dest = $dest;
        $this->constructUsing = $constructUsing;
        $this->convertUsing = $convertUsing;
        $this->beforeMap = $beforeMap;
        $this->afterMap = $afterMap;
    }

    public function getDest(): string
    {
        return $this->dest;
    }

    public function getBeforeMap(): MappingActionInterface|string|null
    {
        return $this->beforeMap;
    }

    public function getAfterMap(): MappingActionInterface|string|null
    {
        return $this->afterMap;
    }

    public function getConvertUsing(): string|TypeConverterInterface|null
    {
        return $this->convertUsing;
    }

    public function getConstructUsing(): string|TypeFactoryInterface|null
    {
        return $this->constructUsing;
    }
}
