<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Fixtures\Mock;

use Backbrain\Automapper\Contract\MappingActionInterface;
use Backbrain\Automapper\Contract\ResolutionContextInterface;

class MappingActionMock implements MappingActionInterface
{
    public ?string $test = null;

    public function __construct(?string $test = null)
    {
        $this->test = $test;
    }

    public function process(mixed $source, mixed $destination, ResolutionContextInterface $context): void
    {
        // noop
    }
}
