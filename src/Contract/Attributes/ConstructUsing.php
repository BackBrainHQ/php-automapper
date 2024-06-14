<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract\Attributes;

use Backbrain\Automapper\Contract\TypeFactoryInterface;

#[\Attribute(\Attribute::TARGET_CLASS)]
class ConstructUsing
{
    /**
     * @var TypeFactoryInterface|class-string<TypeFactoryInterface>
     */
    private TypeFactoryInterface|string $constructUsing;

    /**
     * @param TypeFactoryInterface|class-string<TypeFactoryInterface> $constructUsing Specifies a factory to use to create the destination object
     */
    public function __construct(TypeFactoryInterface|string $constructUsing)
    {
        $this->constructUsing = $constructUsing;
    }

    public function getConstructUsing(): string|TypeFactoryInterface
    {
        return $this->constructUsing;
    }
}
