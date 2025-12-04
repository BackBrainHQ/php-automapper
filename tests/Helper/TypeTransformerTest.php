<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Helper;

use Backbrain\Automapper\Helper\TypeTransformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Type as LegacyType;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\CollectionType;

class TypeTransformerTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists('Symfony\Component\PropertyInfo\Type')) {
            $this->markTestSkipped('Symfony PropertyInfo Type class not found');
        }
    }

    public function testTransformInt(): void
    {
        $legacy = new LegacyType(builtinType: 'int');
        $type = TypeTransformer::toTypeInfoType($legacy);
        $this->assertEquals(Type::int(), $type);
    }

    public function testTransformArrayWithKeyAndValue(): void
    {
        $legacyKey = new LegacyType(builtinType: 'int');
        $legacyValue = new LegacyType(builtinType: 'string');
        $legacy = new LegacyType(
            builtinType: 'array',
            collection: true,
            collectionKeyType: $legacyKey,
            collectionValueType: $legacyValue
        );

        $type = TypeTransformer::toTypeInfoType($legacy);
        $this->assertInstanceOf(CollectionType::class, $type);

        // In TypeInfo, collection types are usually GenericType or CollectionType
        // We expect array<int, string>
        // key type should be int, value type should be string.

        // We can verify via getters if available.
        // Assuming CollectionType has getKeyType() and getVariableTypes() or similar.
        // Let's try to access them.

        $this->assertEquals(Type::int(), $type->getCollectionKeyType());
        $this->assertEquals(Type::string(), $type->getCollectionValueType());
    }

    public function testTransformIterable(): void
    {
        $legacyKey = new LegacyType(builtinType: 'string');
        $legacyValue = new LegacyType(builtinType: 'float');
        $legacy = new LegacyType(
            builtinType: 'iterable',
            collection: true,
            collectionKeyType: $legacyKey,
            collectionValueType: $legacyValue
        );

        $type = TypeTransformer::toTypeInfoType($legacy);
        $this->assertInstanceOf(CollectionType::class, $type);

        $this->assertEquals(Type::string(), $type->getCollectionKeyType());
        $this->assertEquals(Type::float(), $type->getCollectionValueType());
    }

    public function testTransformObjectCollection(): void
    {
        // ArrayObject<int, string>
        $legacyKey = new LegacyType(builtinType: 'int');
        $legacyValue = new LegacyType(builtinType: 'string');
        $legacy = new LegacyType(
            builtinType: 'object',
            class: 'ArrayObject',
            collection: true,
            collectionKeyType: $legacyKey,
            collectionValueType: $legacyValue
        );

        $type = TypeTransformer::toTypeInfoType($legacy);
        $this->assertInstanceOf(CollectionType::class, $type);

        $this->assertEquals(Type::int(), $type->getCollectionKeyType());
        $this->assertEquals(Type::string(), $type->getCollectionValueType());

        // Also check the base type is ArrayObject
        // TypeInfo CollectionType usually wraps a generic or object type?
        // implementation detail: TypeTransformer calls Type::collection($baseType, $value, $key)
    }
}
