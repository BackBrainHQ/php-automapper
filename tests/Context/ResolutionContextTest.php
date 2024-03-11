<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Context;

use Backbrain\Automapper\Context\ResolutionContext;
use Backbrain\Automapper\Contract\AutoMapperInterface;
use Backbrain\Automapper\Contract\MapInterface;
use Backbrain\Automapper\Contract\MemberInterface;
use PHPUnit\Framework\TestCase;

class ResolutionContextTest extends TestCase
{
    public function testResolutionContextReturnsCorrectAutoMapper()
    {
        $autoMapper = $this->createMock(AutoMapperInterface::class);
        $context = new ResolutionContext($autoMapper);

        $this->assertSame($autoMapper, $context->getAutoMapper());
    }

    public function testResolutionContextReturnsCorrectMap()
    {
        $map = $this->createMock(MapInterface::class);
        $context = new ResolutionContext(null, $map);

        $this->assertSame($map, $context->getMap());
    }

    public function testResolutionContextReturnsCorrectMember()
    {
        $member = $this->createMock(MemberInterface::class);
        $context = new ResolutionContext(null, null, $member);

        $this->assertSame($member, $context->getMember());
    }

    public function testResolutionContextReturnsCorrectSource()
    {
        $source = 'source';
        $context = new ResolutionContext(null, null, null, $source);

        $this->assertSame($source, $context->getSource());
    }
}
