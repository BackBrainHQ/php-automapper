<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Converter\Naming;

use Backbrain\Automapper\Converter\Naming\CamelCaseNamingConvention;
use PHPUnit\Framework\TestCase;

class CamelCaseNamingConventionTest extends TestCase
{
    public function testCamelCaseConversionShouldReturnCorrectResult()
    {
        $namingConvention = new CamelCaseNamingConvention();

        $this->assertEquals('helloWorld', $namingConvention->translate('hello_world'));
        $this->assertEquals('testCase', $namingConvention->translate('test_case'));
    }

    public function testCamelCaseConversionShouldHandleSingleWord()
    {
        $namingConvention = new CamelCaseNamingConvention();

        $this->assertEquals('hello', $namingConvention->translate('hello'));
    }

    public function testCamelCaseConversionShouldHandleEmptyString()
    {
        $namingConvention = new CamelCaseNamingConvention();

        $this->assertEquals('', $namingConvention->translate(''));
    }

    public function testCamelCaseConversionShouldHandleNonAlphanumericCharacters()
    {
        $namingConvention = new CamelCaseNamingConvention();

        $this->assertEquals('helloWorld123', $namingConvention->translate('hello_world_123'));
    }
}
