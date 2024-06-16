<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Metadata;

use Backbrain\Automapper\Context\ResolutionContext;
use Backbrain\Automapper\Contract\MapInterface;
use Backbrain\Automapper\Contract\ProfileInterface;
use Backbrain\Automapper\Contract\ValueResolverInterface;
use Backbrain\Automapper\Converter\Naming\CamelCaseNamingConvention;
use Backbrain\Automapper\Converter\Naming\SnakeCaseNamingConvention;
use Backbrain\Automapper\Metadata\ClassMetadataProvider;
use Backbrain\Automapper\Tests\Fixtures\Metadata\Bar\Bar;
use Backbrain\Automapper\Tests\Fixtures\Metadata\Bar\Bazz;
use Backbrain\Automapper\Tests\Fixtures\Metadata\Foo;
use Backbrain\Automapper\Tests\Fixtures\Mock\MappingActionMock;
use Backbrain\Automapper\Tests\Fixtures\Mock\TypeConverterMock;
use Backbrain\Automapper\Tests\Fixtures\Mock\TypeFactoryMock;
use PHPUnit\Framework\TestCase;

class ClassMetadataProviderTest extends TestCase
{
    public function testGetWithMultipleProfiles(): void
    {
        $profiles = (new ClassMetadataProvider())->getProfiles(Foo::class);
        $this->assertNotEmpty($profiles);
        $this->assertInstanceOf(ProfileInterface::class, $profiles[0]);
        $this->assertInstanceOf(ProfileInterface::class, $profiles[1]);

        /** @var MapInterface[] $maps */
        $maps = [...$profiles[0]->getMaps(), ...$profiles[1]->getMaps()];
        $this->assertCount(2, $maps);

        // assertions for the first map
        $this->assertSame(Foo::class, $maps[0]->getSourceType());
        $this->assertSame(Bar::class, $maps[0]->getDestinationType());
        $this->assertInstanceOf(CamelCaseNamingConvention::class, $maps[0]->getSourceMemberNamingConvention());
        $this->assertInstanceOf(SnakeCaseNamingConvention::class, $maps[0]->getDestinationMemberNamingConvention());
        $this->assertInstanceOf(TypeFactoryMock::class, $maps[0]->getTypeFactory());
        $this->assertEquals(Bar::class, $maps[0]->getTypeFactory()->test);
        $this->assertInstanceOf(TypeConverterMock::class, $maps[0]->getTypeConverter());
        $this->assertEquals(Bar::class, $maps[0]->getTypeConverter()->test);
        $this->assertInstanceOf(MappingActionMock::class, $maps[0]->getBeforeMap());
        $this->assertInstanceOf(MappingActionMock::class, $maps[0]->getAfterMap());
        $this->assertEquals(Bar::class, $maps[0]->getBeforeMap()->test);
        $this->assertEquals(Bar::class, $maps[0]->getAfterMap()->test);
        foreach ($maps[0]->getMembers() as $member) {
            $this->assertTrue($member->isIgnored());

            if ('string' === $member->getDestinationProperty()) {
                $this->assertSame('null', $member->getNullSubstitute());
                $this->assertIsCallable($member->getCondition());
                $this->assertEquals('foo-expression', $member->getCondition()(new ($maps[0]->getSourceType()), new ResolutionContext()));
                $this->assertInstanceOf(ValueResolverInterface::class, $member->getValueProvider());
                $this->assertEquals('foo-expression', $member->getValueProvider()->resolve(new ($maps[0]->getSourceType()), new ResolutionContext()));
            }

            if ('float' === $member->getDestinationProperty()) {
                $this->assertSame(0.9, $member->getNullSubstitute());
                $this->assertNull($member->getCondition());
            }
        }

        // assertions for the second map
        $this->assertSame(Foo::class, $maps[1]->getSourceType());
        $this->assertSame(Bazz::class, $maps[1]->getDestinationType());
        $this->assertInstanceOf(CamelCaseNamingConvention::class, $maps[1]->getSourceMemberNamingConvention());
        $this->assertInstanceOf(SnakeCaseNamingConvention::class, $maps[1]->getDestinationMemberNamingConvention());
        $this->assertInstanceOf(TypeFactoryMock::class, $maps[1]->getTypeFactory());
        $this->assertEquals(Bazz::class, $maps[1]->getTypeFactory()->test);
        $this->assertInstanceOf(TypeConverterMock::class, $maps[1]->getTypeConverter());
        $this->assertEquals(Bazz::class, $maps[1]->getTypeConverter()->test);
        $this->assertInstanceOf(MappingActionMock::class, $maps[1]->getBeforeMap());
        $this->assertInstanceOf(MappingActionMock::class, $maps[1]->getAfterMap());
        $this->assertEquals(Bazz::class, $maps[1]->getBeforeMap()->test);
        $this->assertEquals(Bazz::class, $maps[1]->getAfterMap()->test);

        $this->assertEmpty($maps[1]->getMembers());
    }

    public function testGetClassProfilesReturnsEmptyArrayWhenNoProfilesFound(): void
    {
        $provider = new ClassMetadataProvider();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Class NonExistentClass does not exist');
        $provider->getProfiles('NonExistentClass');
    }
}
