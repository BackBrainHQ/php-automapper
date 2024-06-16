<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Fixtures\Mock;

use Backbrain\Automapper\Contract\ResolutionContextInterface;
use Backbrain\Automapper\Contract\TypeFactoryInterface;

class TypeFactoryMock implements TypeFactoryInterface
{
    public ?string $test = null;

    public function __construct(?string $test = null)
    {
        $this->test = $test;
    }

    public function create(mixed $source, ResolutionContextInterface $context): object
    {
        return new \stdClass();
    }
}
