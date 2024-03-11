<?php

declare(strict_types=1);

namespace Backbrain\Automapper;

use Backbrain\Automapper\Builder\ProfileBuilder;
use Backbrain\Automapper\Contract\ProfileInterface;

abstract class Profile extends ProfileBuilder implements ProfileInterface
{
}
