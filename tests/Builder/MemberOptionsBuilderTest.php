<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Builder;

use Backbrain\Automapper\Builder\MemberOptionsBuilder;
use Backbrain\Automapper\Contract\ValueResolverInterface;
use PHPUnit\Framework\TestCase;

class MemberOptionsBuilderTest extends TestCase
{
    public function testMemberOptionsBuilderShouldBuildMember()
    {
        $memberOptionsBuilder = new MemberOptionsBuilder('destinationProperty');

        $this->assertEquals('destinationProperty', $memberOptionsBuilder->build()->getDestinationProperty());
    }

    public function testMemberOptionsBuilderShouldMapFromValueResolverInterface()
    {
        $memberOptionsBuilder = new MemberOptionsBuilder('destinationProperty');
        $valueResolver = $this->createMock(ValueResolverInterface::class);

        $memberOptionsBuilder->mapFrom($valueResolver);

        $this->assertEquals($valueResolver, $memberOptionsBuilder->build()->getValueProvider());
    }

    public function testMemberOptionsBuilderShouldMapFromCallable()
    {
        $memberOptionsBuilder = new MemberOptionsBuilder('destinationProperty');
        $callable = function () { return 'value'; };

        $memberOptionsBuilder->mapFrom($callable);

        $this->assertEquals('value', $memberOptionsBuilder->build()->getValueProvider()->resolve(new \stdClass()));
    }
}
