<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Converter\Naming;

use Backbrain\Automapper\Converter\Naming\PascalCaseNamingConvention;
use PHPUnit\Framework\TestCase;

class PascalCaseNamingConventionTest extends TestCase
{
    public function testPascalCaseConversionShouldReturnCorrectResult()
    {
        $namingConvention = new PascalCaseNamingConvention();

        $this->assertEquals('HelloWorld', $namingConvention->translate('hello_world'));
        $this->assertEquals('TestCase', $namingConvention->translate('test_case'));
    }

    public function testPascalCaseConversionShouldHandleSingleWord()
    {
        $namingConvention = new PascalCaseNamingConvention();

        $this->assertEquals('Hello', $namingConvention->translate('hello'));
    }

    public function testPascalCaseConversionShouldHandleEmptyString()
    {
        $namingConvention = new PascalCaseNamingConvention();

        $this->assertEquals('', $namingConvention->translate(''));
    }

    public function testPascalCaseConversionShouldHandleNonAlphanumericCharacters()
    {
        $namingConvention = new PascalCaseNamingConvention();

        $this->assertEquals('HelloWorld123', $namingConvention->translate('hello_world_123'));
    }
}
