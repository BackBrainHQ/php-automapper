<?php

namespace Backbrain\Automapper\Tests\Fixtures;

interface ScalarDestInterface
{
    public function getAString(): string;

    public function getAnInt(): int;

    public function getAFloat(): float;

    public function isABool(): bool;

    public function getAnArray(): array;
}
