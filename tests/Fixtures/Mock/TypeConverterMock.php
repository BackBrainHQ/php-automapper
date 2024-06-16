<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Fixtures\Mock;

use Backbrain\Automapper\Contract\ResolutionContextInterface;
use Backbrain\Automapper\Contract\TypeConverterInterface;

class TypeConverterMock implements TypeConverterInterface
{
    public mixed $test = null;

    /**
     * @param mixed|null $test
     */
    public function __construct(mixed $test)
    {
        $this->test = $test;
    }

    public function convert(mixed $source, ResolutionContextInterface $context): mixed
    {
        return $this->test;
    }
}
