<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Metadata;

use Backbrain\Automapper\Contract\Attributes\Ignore;
use Backbrain\Automapper\Contract\Attributes\MapTo;
use Backbrain\Automapper\Contract\Attributes\NamingConvention;
use Backbrain\Automapper\Metadata\AttributeMetadataProvider;
use Backbrain\Automapper\Tests\Fixtures\Metadata\Bar\Bar;
use Backbrain\Automapper\Tests\Fixtures\Metadata\Foo;
use PHPUnit\Framework\TestCase;

class AttributeMetadataProviderTests extends TestCase
{
    public function testGetPropertyAttributes(): void
    {
        $attributes = (new AttributeMetadataProvider())->getPropertyAttributes(Foo::class, 'ignoredString');

        $this->assertNotEmpty($attributes, 'No attributes found for the property.');

        $ignoreAttributeFound = false;
        foreach ($attributes as $attribute) {
            if ($attribute instanceof Ignore) {
                $ignoreAttributeFound = true;
                break;
            }
        }

        $this->assertTrue($ignoreAttributeFound, 'Ignore attribute not found for the property.');
    }

    public function testGetClassAttrsNotFound(): void
    {
        $reflectionClass = new \ReflectionClass(Foo::class);
        $attributes = (new AttributeMetadataProvider())->getClassAttrs($reflectionClass, Ignore::class);

        $this->assertEmpty($attributes);
    }

    public function testGetClassAttrs(): void
    {
        $reflectionClass = new \ReflectionClass(Foo::class);
        $attributes = (new AttributeMetadataProvider())->getClassAttrs($reflectionClass, MapTo::class);

        $this->assertNotEmpty($attributes, 'No attributes found for the class.');

        $mapToAttributeFound = false;
        foreach ($attributes as $attribute) {
            if ($attribute instanceof MapTo) {
                $mapToAttributeFound = true;
                $this->assertEquals(Bar::class, $attribute->getDest(), 'MapTo attribute does not have the correct destination.');
                break;
            }
        }

        $this->assertTrue($mapToAttributeFound, 'MapTo attribute not found for the class.');
    }

    public function testGetClassAttrReturnsCorrectAttribute(): void
    {
        $attribute = (new AttributeMetadataProvider())->getClassAttr(Foo::class, NamingConvention::class);
        $this->assertInstanceOf(NamingConvention::class, $attribute);
    }

    public function testGetClassAttrReturnsNullWhenNoAttributeFound(): void
    {
        $attribute = (new AttributeMetadataProvider())->getClassAttr(Foo::class, Ignore::class);
        $this->assertNull($attribute);
    }

    public function testGetClassAttrThrowsExceptionWhenMultipleAttributesFound(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Multiple attributes of type "Backbrain\Automapper\Contract\Attributes\MapTo" found');
        (new AttributeMetadataProvider())->getClassAttr(Foo::class, MapTo::class);
    }
}
