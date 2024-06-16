<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract\Attributes;

use Backbrain\Automapper\Contract\NamingConventionInterface;

#[\Attribute(\Attribute::TARGET_CLASS)]
class NamingConvention
{
    private NamingConventionInterface $namingConvention;

    /**
     * @param NamingConventionInterface $convention Instance of NamingConventionInterface
     */
    public function __construct(NamingConventionInterface $convention)
    {
        $this->namingConvention = $convention;
    }

    public function getNamingConvention(): NamingConventionInterface
    {
        return $this->namingConvention;
    }
}
