<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Helper;

use Backbrain\Automapper\Contract\MappingActionInterface;
use Backbrain\Automapper\Contract\ResolutionContextInterface;
use Backbrain\Automapper\Contract\TypeConverterInterface;
use Backbrain\Automapper\Contract\TypeFactoryInterface;
use Backbrain\Automapper\Helper\Func;
use PHPUnit\Framework\TestCase;

class FuncTest extends TestCase
{
    public function testTypeFactoryFromFnWithFactoryInterface(): void
    {
        $factory = $this->createMock(TypeFactoryInterface::class);
        $result = Func::typeFactoryFromFn($factory);
        $this->assertSame($factory, $result);
    }

    public function testTypeFactoryFromFnWithCallable(): void
    {
        $factory = function ($source, ResolutionContextInterface $context) {
            return $source;
        };
        $result = Func::typeFactoryFromFn($factory);
        $this->assertInstanceOf(TypeFactoryInterface::class, $result);

        $context = $this->createMock(ResolutionContextInterface::class);
        $source = new \stdClass();
        $this->assertSame($source, $result->create($source, $context));
    }

    public function testTypeConverterFromFnWithConverterInterface(): void
    {
        $converter = $this->createMock(TypeConverterInterface::class);
        $result = Func::typeConverterFromFn($converter);
        $this->assertSame($converter, $result);
    }

    public function testTypeConverterFromFnWithCallable(): void
    {
        $converter = function ($source, ResolutionContextInterface $context) {
            return $source;
        };
        $result = Func::typeConverterFromFn($converter);
        $this->assertInstanceOf(TypeConverterInterface::class, $result);

        $context = $this->createMock(ResolutionContextInterface::class);
        $source = new \stdClass();
        $this->assertSame($source, $result->convert($source, $context));
    }

    public function testMappingActionFromFnWithActionInterface(): void
    {
        $action = $this->createMock(MappingActionInterface::class);
        $result = Func::mappingActionFromFn($action);
        $this->assertSame($action, $result);
    }

    public function testMappingActionFromFnWithCallable(): void
    {
        $action = function ($source, $destination, ResolutionContextInterface $context) {
            $destination->value = $source->value;
        };
        $result = Func::mappingActionFromFn($action);
        $this->assertInstanceOf(MappingActionInterface::class, $result);

        $context = $this->createMock(ResolutionContextInterface::class);
        $source = new \stdClass();
        $source->value = 'test';
        $destination = new \stdClass();
        $result->process($source, $destination, $context);
        $this->assertSame('test', $destination->value);
    }
}
