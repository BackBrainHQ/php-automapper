<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Converter\Naming;

use Backbrain\Automapper\Converter\Naming\AdaCaseNamingConvention;
use PHPUnit\Framework\TestCase;

class AdaCaseNamingConventionTest extends TestCase
{
    public function testAdaCaseConversionShouldReturnCorrectResult()
    {
        $namingConvention = new AdaCaseNamingConvention();

        $this->assertEquals('Hello_World', $namingConvention->translate('hello_world'));
        $this->assertEquals('Test_Case', $namingConvention->translate('test_case'));
    }

    public function testAdaCaseConversionShouldHandleSingleWord()
    {
        $namingConvention = new AdaCaseNamingConvention();

        $this->assertEquals('Hello', $namingConvention->translate('hello'));
    }

    public function testAdaCaseConversionShouldHandleEmptyString()
    {
        $namingConvention = new AdaCaseNamingConvention();

        $this->assertEquals('', $namingConvention->translate(''));
    }

    public function testAdaCaseConversionShouldHandleNonAlphanumericCharacters()
    {
        $namingConvention = new AdaCaseNamingConvention();

        $this->assertEquals('Hello_World_123', $namingConvention->translate('hello_world_123'));
    }
}
