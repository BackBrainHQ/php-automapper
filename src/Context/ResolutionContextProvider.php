<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Context;

use Backbrain\Automapper\Contract\AutoMapperInterface;
use Backbrain\Automapper\Contract\MapInterface;
use Backbrain\Automapper\Contract\MemberInterface;
use Backbrain\Automapper\Contract\ResolutionContextInterface;
use Backbrain\Automapper\Contract\ResolutionContextProviderInterface;
use Psr\Container\ContainerInterface;

class ResolutionContextProvider implements ResolutionContextProviderInterface
{
    public function __construct(
        private ?ContainerInterface $container = null,
    ) {
    }

    public function get(
        AutoMapperInterface $autoMapper,
        ?MapInterface $map = null,
        ?MemberInterface $member = null,
        mixed $source = null,
        ?\ArrayAccess $vars = null,
    ): ResolutionContextInterface {
        $vars = $vars ?? new \ArrayObject();
        $vars['container'] = $vars['container'] ?? $this->container;

        return new ResolutionContext(
            autoMapper: $autoMapper,
            map: $map,
            member: $member,
            source: $source,
            vars: $vars
        );
    }
}
