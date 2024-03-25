<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Builder;

use Backbrain\Automapper\Builder\DefaultOptionsBuilder;
use Backbrain\Automapper\Context\ResolutionContext;
use Backbrain\Automapper\Contract\ValueResolverInterface;
use PHPUnit\Framework\TestCase;

class MemberOptionsBuilderTest extends TestCase
{
    public function testMemberOptionsBuilderShouldBuildMember()
    {
        $memberOptionsBuilder = new DefaultOptionsBuilder('destinationProperty');

        $this->assertEquals('destinationProperty', $memberOptionsBuilder->build()->getDestinationProperty());
    }

    public function testMemberOptionsBuilderShouldMapFromValueResolverInterface()
    {
        $memberOptionsBuilder = new DefaultOptionsBuilder('destinationProperty');
        $valueResolver = $this->createMock(ValueResolverInterface::class);

        $memberOptionsBuilder->mapFrom($valueResolver);

        $this->assertEquals($valueResolver, $memberOptionsBuilder->build()->getValueProvider());
    }

    public function testMemberOptionsBuilderShouldMapFromCallable()
    {
        $memberOptionsBuilder = new DefaultOptionsBuilder('destinationProperty');
        $callable = function () { return 'value'; };

        $memberOptionsBuilder->mapFrom($callable);

        $this->assertEquals('value', $memberOptionsBuilder->build()->getValueProvider()->resolve(new \stdClass(), new ResolutionContext()));
    }
}
