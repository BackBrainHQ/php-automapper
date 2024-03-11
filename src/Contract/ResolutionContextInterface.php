<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Contract;

interface ResolutionContextInterface
{
    public function getAutoMapper(): ?AutoMapperInterface;

    public function getSource(): mixed;

    public function getMember(): ?MemberInterface;

    public function getMap(): ?MapInterface;
}
