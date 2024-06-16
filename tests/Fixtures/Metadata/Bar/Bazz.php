<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Fixtures\Metadata\Bar;

use Backbrain\Automapper\Contract\Attributes\ConstructUsing;
use Backbrain\Automapper\Contract\Attributes\NamingConvention;
use Backbrain\Automapper\Converter\Naming\SnakeCaseNamingConvention;
use Backbrain\Automapper\Tests\Fixtures\Mock\TypeFactoryMock;

#[NamingConvention(new SnakeCaseNamingConvention())]
#[ConstructUsing(new TypeFactoryMock(self::class))]
class Bazz
{
}
