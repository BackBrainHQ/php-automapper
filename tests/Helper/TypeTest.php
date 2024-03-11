<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Helper;

use Backbrain\Automapper\Helper\Type;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    public function testTypeToStringReturnsCorrectStringForBuiltinTypes()
    {
        $type = new \Symfony\Component\PropertyInfo\Type(builtinType: \Symfony\Component\PropertyInfo\Type::BUILTIN_TYPE_INT);
        $this->assertEquals('int', Type::toString($type));
    }

    public function testTypeToStringReturnsCorrectStringForClassTypes()
    {
        $type = new \Symfony\Component\PropertyInfo\Type(builtinType: \Symfony\Component\PropertyInfo\Type::BUILTIN_TYPE_OBJECT, class: 'DateTime');
        $this->assertEquals('DateTime', Type::toString($type));
    }

    public function testTypeToStringReturnsCorrectStringForCollectionTypes()
    {
        $type = new \Symfony\Component\PropertyInfo\Type(
            builtinType: \Symfony\Component\PropertyInfo\Type::BUILTIN_TYPE_ARRAY,
            collection: true,
            collectionValueType: new \Symfony\Component\PropertyInfo\Type(builtinType: \Symfony\Component\PropertyInfo\Type::BUILTIN_TYPE_INT));

        $this->assertEquals('array<int>', Type::toString($type));
    }

    public function testTypeToStringReturnsCorrectStringForMixedTypes()
    {
        $type = 'string';
        $this->assertEquals('string', Type::toString($type));
    }

    public function testArrayOfReturnsCorrectString()
    {
        $this->assertEquals('array<DateTime>', Type::arrayOf('DateTime'));
    }
}
