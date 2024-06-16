<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract\Attributes;

use Backbrain\Automapper\Contract\MappingActionInterface;
use Backbrain\Automapper\Contract\TypeConverterInterface;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class MapTo
{
    private string $dest;

    private ?MappingActionInterface $beforeMap;

    private ?MappingActionInterface $afterMap;

    private ?TypeConverterInterface $convertUsing;

    /**
     * @param string                      $dest         The destination type name
     * @param TypeConverterInterface|null $convertUsing Specifies a converter to use to convert the source object
     * @param MappingActionInterface|null $beforeMap    Run this action before mapping
     * @param MappingActionInterface|null $afterMap     Run this action after mapping
     */
    public function __construct(string $dest, ?TypeConverterInterface $convertUsing = null, ?MappingActionInterface $beforeMap = null, ?MappingActionInterface $afterMap = null)
    {
        $this->dest = $dest;
        $this->convertUsing = $convertUsing;
        $this->beforeMap = $beforeMap;
        $this->afterMap = $afterMap;
    }

    public function getDest(): string
    {
        return $this->dest;
    }

    public function getBeforeMap(): ?MappingActionInterface
    {
        return $this->beforeMap;
    }

    public function getAfterMap(): ?MappingActionInterface
    {
        return $this->afterMap;
    }

    public function getConvertUsing(): ?TypeConverterInterface
    {
        return $this->convertUsing;
    }
}
