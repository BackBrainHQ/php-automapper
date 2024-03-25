<?php

declare(strict_types=1);

namespace Backbrain\Automapper;

use Backbrain\Automapper\Builder\DefaultConfig;
use Backbrain\Automapper\Contract\ProfileInterface;

abstract class Profile extends DefaultConfig implements ProfileInterface
{
}
