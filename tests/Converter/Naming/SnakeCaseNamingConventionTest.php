<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Converter\Naming;

use Backbrain\Automapper\Converter\Naming\SnakeCaseNamingConvention;
use PHPUnit\Framework\TestCase;

class SnakeCaseNamingConventionTest extends TestCase
{
    public function testSnakeCaseConversionShouldReturnCorrectResult()
    {
        $namingConvention = new SnakeCaseNamingConvention();

        $this->assertEquals('hello_world', $namingConvention->translate('HelloWorld'));
        $this->assertEquals('test_case', $namingConvention->translate('TestCase'));
    }

    public function testSnakeCaseConversionShouldHandleSingleWord()
    {
        $namingConvention = new SnakeCaseNamingConvention();

        $this->assertEquals('hello', $namingConvention->translate('Hello'));
    }

    public function testSnakeCaseConversionShouldHandleEmptyString()
    {
        $namingConvention = new SnakeCaseNamingConvention();

        $this->assertEquals('', $namingConvention->translate(''));
    }

    public function testSnakeCaseConversionShouldHandleNonAlphanumericCharacters()
    {
        $namingConvention = new SnakeCaseNamingConvention();

        $this->assertEquals('hello_world123', $namingConvention->translate('HelloWorld123'));
    }
}
