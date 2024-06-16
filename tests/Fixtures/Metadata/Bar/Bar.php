<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Fixtures\Metadata\Bar;

use Backbrain\Automapper\Contract\Attributes\Condition;
use Backbrain\Automapper\Contract\Attributes\ConstructUsing;
use Backbrain\Automapper\Contract\Attributes\Ignore;
use Backbrain\Automapper\Contract\Attributes\MapFrom;
use Backbrain\Automapper\Contract\Attributes\NamingConvention;
use Backbrain\Automapper\Contract\Attributes\NullSubstitute;
use Backbrain\Automapper\Converter\Naming\SnakeCaseNamingConvention;
use Backbrain\Automapper\Tests\Fixtures\Metadata\Foo;
use Backbrain\Automapper\Tests\Fixtures\Mock\TypeFactoryMock;
use Symfony\Component\ExpressionLanguage\Expression;

#[NamingConvention(new SnakeCaseNamingConvention())]
#[ConstructUsing(new TypeFactoryMock(self::class))]
class Bar
{
    #[Ignore]
    #[NullSubstitute('null')]
    #[Condition(Foo::class, new Expression('"foo-expression"'))]
    #[Condition(Bazz::class, new Expression('"bazz-expression"'))]
    #[MapFrom(Foo::class, new Expression('"foo-expression"'))]
    #[MapFrom(Bazz::class, new Expression('"bazz-expression"'))]
    public string $string;

    #[Ignore]
    #[NullSubstitute(0.9)]
    public float $float;
}
