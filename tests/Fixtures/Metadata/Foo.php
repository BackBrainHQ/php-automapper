<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Fixtures\Metadata;

use Backbrain\Automapper\Contract\Attributes\ConstructUsing;
use Backbrain\Automapper\Contract\Attributes\Ignore;
use Backbrain\Automapper\Contract\Attributes\MapTo;
use Backbrain\Automapper\Contract\Attributes\NamingConvention;
use Backbrain\Automapper\Converter\Naming\CamelCaseNamingConvention;
use Backbrain\Automapper\Tests\Fixtures\Metadata\Bar\Bar;
use Backbrain\Automapper\Tests\Fixtures\Metadata\Bar\Bazz;
use Backbrain\Automapper\Tests\Fixtures\Mock\MappingActionMock;
use Backbrain\Automapper\Tests\Fixtures\Mock\TypeConverterMock;
use Backbrain\Automapper\Tests\Fixtures\Mock\TypeFactoryMock;

#[MapTo(Bar::class, beforeMap: new MappingActionMock(Bar::class), afterMap: new MappingActionMock(Bar::class), convertUsing: new TypeConverterMock(Bar::class))]
#[MapTo(Bazz::class, beforeMap: new MappingActionMock(Bazz::class), afterMap: new MappingActionMock(Bazz::class), convertUsing: new TypeConverterMock(Bazz::class))]
#[NamingConvention(new CamelCaseNamingConvention())]
#[ConstructUsing(new TypeFactoryMock(self::class))]
class Foo
{
    #[Ignore]
    public string $ignoredString;
}
