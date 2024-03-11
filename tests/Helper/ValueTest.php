<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Helper;

use Backbrain\Automapper\Helper\Value;
use PHPUnit\Framework\TestCase;

class ValueTest extends TestCase
{
    public function testAsIntShouldReturnIntWhenIntIsGiven()
    {
        $this->assertSame(1, Value::asInt(1));
    }

    public function testAsIntShouldThrowExceptionWhenNonIntIsGiven()
    {
        $this->expectException(\InvalidArgumentException::class);
        Value::asInt('string');
    }

    public function testAsFloatShouldReturnFloatWhenFloatIsGiven()
    {
        $this->assertSame(1.0, Value::asFloat(1.0));
    }

    public function testAsFloatShouldThrowExceptionWhenNonFloatIsGiven()
    {
        $this->expectException(\InvalidArgumentException::class);
        Value::asFloat('string');
    }

    public function testAsStringShouldReturnStringWhenStringIsGiven()
    {
        $this->assertSame('string', Value::asString('string'));
    }

    public function testAsStringShouldThrowExceptionWhenNonStringIsGiven()
    {
        $this->expectException(\InvalidArgumentException::class);
        Value::asString(1);
    }

    public function testAsBoolShouldReturnBoolWhenBoolIsGiven()
    {
        $this->assertSame(true, Value::asBool(true));
    }

    public function testAsBoolShouldThrowExceptionWhenNonBoolIsGiven()
    {
        $this->expectException(\InvalidArgumentException::class);
        Value::asBool('string');
    }

    public function testAsObjectShouldReturnObjectWhenObjectIsGiven()
    {
        $object = new \stdClass();
        $this->assertSame($object, Value::asObject($object, \stdClass::class));
    }

    public function testAsObjectShouldThrowExceptionWhenNonObjectIsGiven()
    {
        $this->expectException(\InvalidArgumentException::class);
        Value::asObject('string', \stdClass::class);
    }

    public function testAsObjectShouldThrowExceptionWhenObjectOfDifferentClassIsGiven()
    {
        $this->expectException(\InvalidArgumentException::class);
        Value::asObject(new \stdClass(), \Exception::class);
    }
}
