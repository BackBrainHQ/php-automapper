<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Helper;

use Backbrain\Automapper\Helper\Type;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    public function testToStringPropertyInfo()
    {
        if (!class_exists(\Symfony\Component\PropertyInfo\Type::class)) {
            $this->markTestSkipped('PropertyInfo not available');
        }

        // Int
        $type = new \Symfony\Component\PropertyInfo\Type(\Symfony\Component\PropertyInfo\Type::BUILTIN_TYPE_INT);
        $this->assertEquals('int', Type::toString($type));

        // Object
        $type = new \Symfony\Component\PropertyInfo\Type(\Symfony\Component\PropertyInfo\Type::BUILTIN_TYPE_OBJECT, false, 'DateTime');
        $this->assertEquals('DateTime', Type::toString($type));

        // Array
        $type = new \Symfony\Component\PropertyInfo\Type(\Symfony\Component\PropertyInfo\Type::BUILTIN_TYPE_ARRAY, false, null, true);
        $this->assertEquals('array<mixed>', Type::toString($type));

        // Collection
        $type = new \Symfony\Component\PropertyInfo\Type(
            \Symfony\Component\PropertyInfo\Type::BUILTIN_TYPE_ARRAY,
            false,
            null,
            true,
            new \Symfony\Component\PropertyInfo\Type(\Symfony\Component\PropertyInfo\Type::BUILTIN_TYPE_STRING),
            new \Symfony\Component\PropertyInfo\Type(\Symfony\Component\PropertyInfo\Type::BUILTIN_TYPE_INT)
        );
        $this->assertEquals('array<string, int>', Type::toString($type));

        // Array of
        $type = new \Symfony\Component\PropertyInfo\Type(
            \Symfony\Component\PropertyInfo\Type::BUILTIN_TYPE_ARRAY,
            false,
            null,
            true,
            null,
            new \Symfony\Component\PropertyInfo\Type(\Symfony\Component\PropertyInfo\Type::BUILTIN_TYPE_INT)
        );
        $this->assertEquals('array<int>', Type::toString($type));
    }

    public function testToStringTypeInfo()
    {
        // Int
        $type = \Symfony\Component\TypeInfo\Type::int();
        $this->assertEquals('int', Type::toString($type));

        // Object
        $type = \Symfony\Component\TypeInfo\Type::object('DateTime');
        $this->assertEquals('DateTime', Type::toString($type));

        // Collection
        $type = \Symfony\Component\TypeInfo\Type::list(\Symfony\Component\TypeInfo\Type::int());
        $this->assertEquals('array<int>', Type::toString($type));

        $type = \Symfony\Component\TypeInfo\Type::array(\Symfony\Component\TypeInfo\Type::int());
        $this->assertEquals('array<int>', Type::toString($type));

        // Collection with key
        // We use string as key, int as value
        // Note: Symfony TypeInfo < 7.2 might have different signature for Type::array or behavior
        // So we use one-arg array for safety in this test across versions for now,
        // or specific construction if we wanted to be precise.
        // Let's just test normalization of array<int, string> -> array<string>
        $type = \Symfony\Component\TypeInfo\Type::array(
            \Symfony\Component\TypeInfo\Type::string() // value
        );
        // Default key is int. So array<int, string> -> normalized to array<string>
        $this->assertEquals('array<string>', Type::toString($type));
    }

    public function testGetBuiltinTypePropertyInfo()
    {
        if (!class_exists(\Symfony\Component\PropertyInfo\Type::class)) {
            $this->markTestSkipped('PropertyInfo not available');
        }

        $type = new \Symfony\Component\PropertyInfo\Type(\Symfony\Component\PropertyInfo\Type::BUILTIN_TYPE_INT);
        $this->assertEquals('int', Type::getBuiltinType($type));
    }

    public function testGetBuiltinTypeTypeInfo()
    {
        $type = \Symfony\Component\TypeInfo\Type::int();
        $this->assertEquals('int', Type::getBuiltinType($type));

        $type = \Symfony\Component\TypeInfo\Type::object('DateTime');
        $this->assertEquals('object', Type::getBuiltinType($type));

        $type = \Symfony\Component\TypeInfo\Type::null();
        $this->assertEquals('null', Type::getBuiltinType($type));

        $type = \Symfony\Component\TypeInfo\Type::list(\Symfony\Component\TypeInfo\Type::int());
        $this->assertEquals('array', Type::getBuiltinType($type));

        // CollectionType wraps a type. If it wraps array, it returns array.
        // Type::list() returns a CollectionType wrapping array/list.

        $type = \Symfony\Component\TypeInfo\Type::union(\Symfony\Component\TypeInfo\Type::int(), \Symfony\Component\TypeInfo\Type::string());
        $this->assertEquals('mixed', Type::getBuiltinType($type));
    }

    public function testGetClassNamePropertyInfo()
    {
        if (!class_exists(\Symfony\Component\PropertyInfo\Type::class)) {
            $this->markTestSkipped('PropertyInfo not available');
        }

        $type = new \Symfony\Component\PropertyInfo\Type(\Symfony\Component\PropertyInfo\Type::BUILTIN_TYPE_OBJECT, false, 'DateTime');
        $this->assertEquals('DateTime', Type::getClassName($type));
    }

    public function testGetClassNameTypeInfo()
    {
        $type = \Symfony\Component\TypeInfo\Type::object('DateTime');
        $this->assertEquals('DateTime', Type::getClassName($type));

        $type = \Symfony\Component\TypeInfo\Type::int();
        $this->assertNull(Type::getClassName($type));

        // Generic wrapping object
        $type = \Symfony\Component\TypeInfo\Type::generic(
            \Symfony\Component\TypeInfo\Type::object('Iterator'),
            \Symfony\Component\TypeInfo\Type::string()
        );
        $this->assertEquals('Iterator', Type::getClassName($type));
    }

    public function testIsCollectionPropertyInfo()
    {
        if (!class_exists(\Symfony\Component\PropertyInfo\Type::class)) {
            $this->markTestSkipped('PropertyInfo not available');
        }

        $type = new \Symfony\Component\PropertyInfo\Type(\Symfony\Component\PropertyInfo\Type::BUILTIN_TYPE_ARRAY, false, null, true);
        $this->assertTrue(Type::isCollection($type));

        $type = new \Symfony\Component\PropertyInfo\Type(\Symfony\Component\PropertyInfo\Type::BUILTIN_TYPE_INT);
        $this->assertFalse(Type::isCollection($type));
    }

    public function testIsCollectionTypeInfo()
    {
        $type = \Symfony\Component\TypeInfo\Type::list(\Symfony\Component\TypeInfo\Type::int());
        $this->assertTrue(Type::isCollection($type));

        $type = \Symfony\Component\TypeInfo\Type::collection(\Symfony\Component\TypeInfo\Type::object(\ArrayObject::class));
        $this->assertTrue(Type::isCollection($type));

        $type = \Symfony\Component\TypeInfo\Type::int();
        $this->assertFalse(Type::isCollection($type));

        // Traversable object
        $type = \Symfony\Component\TypeInfo\Type::object(\Iterator::class);
        $this->assertTrue(Type::isCollection($type));
    }

    public function testGetCollectionKeyTypesTypeInfo()
    {
        // Array<string, int>
        $type = \Symfony\Component\TypeInfo\Type::array(\Symfony\Component\TypeInfo\Type::int(), \Symfony\Component\TypeInfo\Type::string());
        $keys = Type::getCollectionKeyTypes($type);
        $this->assertCount(1, $keys);
        $this->assertEquals('string', Type::toString($keys[0]));

        // List<int> -> key is int
        $type = \Symfony\Component\TypeInfo\Type::list(\Symfony\Component\TypeInfo\Type::int());
        $keys = Type::getCollectionKeyTypes($type);
        $this->assertCount(1, $keys);
        $this->assertEquals('int', Type::toString($keys[0]));
    }

    public function testGetCollectionValueTypesTypeInfo()
    {
        $type = \Symfony\Component\TypeInfo\Type::array(\Symfony\Component\TypeInfo\Type::int(), \Symfony\Component\TypeInfo\Type::string());
        $values = Type::getCollectionValueTypes($type);
        $this->assertCount(1, $values);
        $this->assertEquals('int', Type::toString($values[0]));
    }

    public function testIsNullable()
    {
        if (class_exists(\Symfony\Component\PropertyInfo\Type::class)) {
            $type = new \Symfony\Component\PropertyInfo\Type(\Symfony\Component\PropertyInfo\Type::BUILTIN_TYPE_INT, true);
            $this->assertTrue(Type::isNullable($type));
        }

        $type = \Symfony\Component\TypeInfo\Type::nullable(\Symfony\Component\TypeInfo\Type::int());
        $this->assertTrue(Type::isNullable($type));

        $type = \Symfony\Component\TypeInfo\Type::int();
        $this->assertFalse(Type::isNullable($type));
    }

    public function testGenericTypeHelpers()
    {
        // Helper for GenericType tests
        // Generic wrapping Object with 2 vars (key, value)
        // class MyMap<K, V> {}

        $generic = \Symfony\Component\TypeInfo\Type::generic(
            \Symfony\Component\TypeInfo\Type::object('MyMap'),
            \Symfony\Component\TypeInfo\Type::string(),
            \Symfony\Component\TypeInfo\Type::int()
        );

        // getKeyTypes should return first var?
        // Implementation says: if count(vars) >= 2 return vars[0]
        $keys = Type::getCollectionKeyTypes($generic);
        $this->assertCount(1, $keys);
        $this->assertEquals('string', Type::toString($keys[0]));

        // getValueTypes should return second var?
        // Implementation says: if count(vars) >= 2 return vars[1]
        $values = Type::getCollectionValueTypes($generic);
        $this->assertCount(1, $values);
        $this->assertEquals('int', Type::toString($values[0]));
    }

    public function testMapOf()
    {
        $this->assertEquals('array<string,int>', Type::mapOf(['string'], ['int']));
    }

    public function testCollectionOf()
    {
        $this->assertEquals('MyCollection<string,int>', Type::collectionOf('MyCollection', ['string'], ['int']));
    }
}
