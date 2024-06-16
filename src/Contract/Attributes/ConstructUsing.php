<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract\Attributes;

use Backbrain\Automapper\Contract\TypeFactoryInterface;

#[\Attribute(\Attribute::TARGET_CLASS)]
class ConstructUsing
{
    private TypeFactoryInterface $constructUsing;

    /**
     * @param TypeFactoryInterface $constructUsing Specifies a factory to use to create the destination object
     */
    public function __construct(TypeFactoryInterface $constructUsing)
    {
        $this->constructUsing = $constructUsing;
    }

    public function getConstructUsing(): TypeFactoryInterface
    {
        return $this->constructUsing;
    }
}
