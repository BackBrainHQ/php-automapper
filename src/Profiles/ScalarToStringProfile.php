<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Profiles;

use Backbrain\Automapper\Contract\ResolutionContextInterface;
use Backbrain\Automapper\Helper\Value;
use Backbrain\Automapper\Profile;

class ScalarToStringProfile extends Profile
{
    public function __construct()
    {
        $this
            ->createMap('int', 'string')
                ->convertUsing(fn (mixed $source, ResolutionContextInterface $context): string => sprintf('%d', Value::asInt($source)))
            ->createMap('float', 'string')
                ->convertUsing(fn (mixed $source, ResolutionContextInterface $context): string => sprintf('%f', Value::asFloat($source)))
            ->createMap('bool', 'string')
                ->convertUsing(fn (mixed $source, ResolutionContextInterface $context): string => Value::asBool($source) ? 'true' : 'false')
        ;
    }
}
