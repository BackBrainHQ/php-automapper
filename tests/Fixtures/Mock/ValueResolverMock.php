<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Fixtures\Mock;

use Backbrain\Automapper\Contract\ResolutionContextInterface;
use Backbrain\Automapper\Contract\ValueResolverInterface;

class ValueResolverMock implements ValueResolverInterface
{
    public function resolve(object $source, ResolutionContextInterface $context): mixed
    {
        return null;
    }
}
