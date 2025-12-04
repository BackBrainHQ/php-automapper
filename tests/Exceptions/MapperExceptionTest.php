<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Exceptions;

use Backbrain\Automapper\Context\MappingContext;
use Backbrain\Automapper\Exceptions\MapperException;
use PHPUnit\Framework\TestCase;

class MapperExceptionTest extends TestCase
{
    public function testNewMissingMapException(): void
    {
        $exception = MapperException::newMissingMapException('Source', 'Dest');
        $this->assertInstanceOf(MapperException::class, $exception);
        $this->assertSame(MapperException::MISSING_MAP, $exception->getCode());
        $this->assertStringContainsString('No mapping found for source type "Source" and destination type "Dest"', $exception->getMessage());
    }

    public function testNewMissingMapExceptionWithContext(): void
    {
        $context = MappingContext::root('someSourceValue', 'DestClass');

        $exception = MapperException::newMissingMapException('Source', 'Dest', $context);
        $this->assertStringContainsString('Context: {', $exception->getMessage());
        $this->assertStringContainsString('dest=DestClass', $exception->getMessage());
    }

    public function testNewMissingMapsExceptionSingle(): void
    {
        $exception = MapperException::newMissingMapsException('Source', ['Dest']);
        $this->assertSame(MapperException::MISSING_MAP, $exception->getCode());
        $this->assertStringContainsString('No mapping found for source type "Source" to destination type "Dest"', $exception->getMessage());
    }

    public function testNewMissingMapsExceptionMultiple(): void
    {
        $exception = MapperException::newMissingMapsException('Source', ['Dest1', 'Dest2']);
        $this->assertSame(MapperException::MISSING_MAP, $exception->getCode());
        $this->assertStringContainsString('No mapping found for source type "Source" to any of the destination types "Dest1", "Dest2"', $exception->getMessage());
    }

    public function testNewDestinationClassNotFoundException(): void
    {
        $exception = MapperException::newDestinationClassNotFoundException('Dest');
        $this->assertSame(MapperException::CLASS_NOT_FOUND, $exception->getCode());
        $this->assertStringContainsString('Class for destination type "Dest" does not exist', $exception->getMessage());
    }

    public function testNewUnexpectedTypeException(): void
    {
        $exception = MapperException::newUnexpectedTypeException('int', 'string');
        $this->assertSame(MapperException::UNEXPECTED_TYPE, $exception->getCode());
        $this->assertStringContainsString('Expected "int", got "string"', $exception->getMessage());
    }

    public function testNewCollectionNotWriteableException(): void
    {
        $exception = MapperException::newCollectionNotWriteableException('ArrayObject');
        $this->assertSame(MapperException::COLLECTION_NOT_WRITEABLE, $exception->getCode());
        $this->assertStringContainsString('Collection of type "ArrayObject" is not writeable', $exception->getMessage());
    }

    public function testNewCircularDependencyException(): void
    {
        $exception = MapperException::newCircularDependencyException(['A', 'B'], 'Source', 'MappedBy');
        $this->assertSame(MapperException::CIRCULAR_DEPENDENCY, $exception->getCode());
        $this->assertStringContainsString('Circular dependency detected in mapping stack: "A -> B"', $exception->getMessage());
    }

    public function testNewIllegalTypeException(): void
    {
        $exception = MapperException::newIllegalTypeException('BadType');
        $this->assertSame(MapperException::ILLEGAL_TYPE_EXPRESSION, $exception->getCode());
        $this->assertStringContainsString('Illegal type expression "BadType"', $exception->getMessage());
    }

    public function testNewInstantiationFailedException(): void
    {
        $exception = MapperException::newInstantiationFailedException('Dest', 'Source');
        $this->assertSame(MapperException::INSTANTIATION_FAILED, $exception->getCode());
        $this->assertStringContainsString('Instantiation of type "Dest" failed', $exception->getMessage());
    }
}
