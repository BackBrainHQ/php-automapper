<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Context;

use Backbrain\Automapper\Contract\AutoMapperInterface;
use Backbrain\Automapper\Contract\MapInterface;
use Backbrain\Automapper\Contract\MemberInterface;
use Backbrain\Automapper\Contract\ResolutionContextInterface;

readonly class ResolutionContext implements ResolutionContextInterface
{
    public function __construct(
        private ?AutoMapperInterface $autoMapper = null,
        private ?MapInterface $map = null,
        private ?MemberInterface $member = null,
        private mixed $source = null,
    ) {
    }

    public function getAutoMapper(): ?AutoMapperInterface
    {
        return $this->autoMapper;
    }

    public function getSource(): mixed
    {
        return $this->source;
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
