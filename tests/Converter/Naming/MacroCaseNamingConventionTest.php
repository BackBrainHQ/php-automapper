<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Converter\Naming;

use Backbrain\Automapper\Converter\Naming\MacroCaseNamingConvention;
use PHPUnit\Framework\TestCase;

class MacroCaseNamingConventionTest extends TestCase
{
    public function testMacroCaseConversionShouldReturnCorrectResult()
    {
        $namingConvention = new MacroCaseNamingConvention();

        $this->assertEquals('HELLO_WORLD', $namingConvention->translate('hello_world'));
        $this->assertEquals('TEST_CASE', $namingConvention->translate('test_case'));
    }

    public function testMacroCaseConversionShouldHandleSingleWord()
    {
        $namingConvention = new MacroCaseNamingConvention();

        $this->assertEquals('HELLO', $namingConvention->translate('hello'));
    }

    public function testMacroCaseConversionShouldHandleEmptyString()
    {
        $namingConvention = new MacroCaseNamingConvention();

        $this->assertEquals('', $namingConvention->translate(''));
    }

    public function testMacroCaseConversionShouldHandleNonAlphanumericCharacters()
    {
        $namingConvention = new MacroCaseNamingConvention();

        $this->assertEquals('HELLO_WORLD_123', $namingConvention->translate('hello_world_123'));
    }
}
