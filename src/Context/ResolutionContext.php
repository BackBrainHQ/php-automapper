<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Context;

use Backbrain\Automapper\Contract\AutoMapperInterface;
use Backbrain\Automapper\Contract\MapInterface;
use Backbrain\Automapper\Contract\MemberInterface;
use Backbrain\Automapper\Contract\ResolutionContextInterface;

class ResolutionContext implements ResolutionContextInterface
{
    /**
     * @var \ArrayAccess<string, mixed>
     */
    private \ArrayAccess $vars;

    /**
     * @param \ArrayAccess<string, mixed>|null $vars
     */
    public function __construct(
        private readonly ?AutoMapperInterface $autoMapper = null,
        private readonly ?MapInterface $map = null,
        private readonly ?MemberInterface $member = null,
        private readonly mixed $source = null,
        ?\ArrayAccess $vars = null,
    ) {
        $this->vars = $vars ?? new \ArrayObject();
    }

    public function getAutoMapper(): ?AutoMapperInterface
    {
        return $this->autoMapper;
    }

    public function getSource(): mixed
    {
        return $this->source;
    }

    /**
     * Returns the variables associated with this context.
     *
     * @return \ArrayAccess<string, mixed> the variables
     */
    public function getVars(): \ArrayAccess
    {
        return $this->vars;
    }

    public function getMember(): ?MemberInterface
    {
        return $this->member;
    }

    public function getMap(): ?MapInterface
    {
        return $this->map;
    }
}
