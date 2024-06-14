<?php

namespace Backbrain\Automapper\Contract\Attributes;

use Backbrain\Automapper\Contract\NamingConventionInterface;

#[\Attribute(\Attribute::TARGET_CLASS)]
class NamingConvention
{
    /**
     * @var NamingConventionInterface|class-string
     */
    private NamingConventionInterface|string $namingConvention;

    /**
     * @param NamingConventionInterface|class-string $convention Instance or class name of the naming convention to use
     */
    public function __construct(NamingConventionInterface|string $convention)
    {
        $this->namingConvention = $convention;
    }

    public function getNamingConvention(): NamingConventionInterface|string
    {
        return $this->namingConvention;
    }
}
