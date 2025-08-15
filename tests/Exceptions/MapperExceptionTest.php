<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Exceptions;

use Backbrain\Automapper\AutoMapper;
use Backbrain\Automapper\Contract\Builder\Config;
use Backbrain\Automapper\Exceptions\MapperException;
use Backbrain\Automapper\MapperConfiguration;
use PHPUnit\Framework\TestCase;

class MapperExceptionTest extends TestCase
{
    public function testNewMissingMapExceptionWithContext(): void
    {
        $exception = MapperException::newMissingMapExceptionWithContext(
            'SourceType',
            'DestinationType',
            'SourceClass',
            'DestinationClass',
            'mapping operation'
        );

        $message = $exception->getMessage();

        // Check that the message contains all the expected context information
        $this->assertStringContainsString('SourceType', $message);
        $this->assertStringContainsString('DestinationType', $message);
        $this->assertStringContainsString('source class: SourceClass', $message);
        $this->assertStringContainsString('destination class: DestinationClass', $message);
        $this->assertStringContainsString('mapping operation', $message);
        $this->assertEquals(MapperException::MISSING_MAP, $exception->getCode());
    }

    public function testNewMissingMapExceptionWithContextSourceClassOnly(): void
    {
        $exception = MapperException::newMissingMapExceptionWithContext(
            'SourceType',
            'DestinationType',
            'SourceClass',
            null
        );

        $message = $exception->getMessage();

        $this->assertStringContainsString('SourceType', $message);
        $this->assertStringContainsString('DestinationType', $message);
        $this->assertStringContainsString('source class: SourceClass', $message);
        $this->assertStringNotContainsString('destination class:', $message);
    }

    public function testNewMissingMapExceptionWithContextDestinationClassOnly(): void
    {
        $exception = MapperException::newMissingMapExceptionWithContext(
            'SourceType',
            'DestinationType',
            null,
            'DestinationClass'
        );

        $message = $exception->getMessage();

        $this->assertStringContainsString('SourceType', $message);
        $this->assertStringContainsString('DestinationType', $message);
        $this->assertStringContainsString('destination class: DestinationClass', $message);
        $this->assertStringNotContainsString('source class:', $message);
    }

    public function testNewMissingMapExceptionWithContextMinimal(): void
    {
        $exception = MapperException::newMissingMapExceptionWithContext(
            'SourceType',
            'DestinationType'
        );

        $message = $exception->getMessage();

        $this->assertStringContainsString('SourceType', $message);
        $this->assertStringContainsString('DestinationType', $message);
        $this->assertStringNotContainsString('source class:', $message);
        $this->assertStringNotContainsString('destination class:', $message);
        $this->assertStringNotContainsString('Context:', $message);
    }

    public function testNewMissingMapsExceptionWithContextSingleType(): void
    {
        $exception = MapperException::newMissingMapsExceptionWithContext(
            'SourceType',
            ['DestinationType'],
            'DestinationClass',
            'propertyName'
        );

        $message = $exception->getMessage();

        $this->assertStringContainsString('SourceType', $message);
        $this->assertStringContainsString('DestinationType', $message);
        $this->assertStringContainsString('destination class: DestinationClass', $message);
        $this->assertStringContainsString('property: propertyName', $message);
        $this->assertEquals(MapperException::MISSING_MAP, $exception->getCode());
    }

    public function testNewMissingMapsExceptionWithContextMultipleTypes(): void
    {
        $exception = MapperException::newMissingMapsExceptionWithContext(
            'SourceType',
            ['DestinationType1', 'DestinationType2'],
            'DestinationClass',
            'propertyName'
        );

        $message = $exception->getMessage();

        $this->assertStringContainsString('SourceType', $message);
        $this->assertStringContainsString('DestinationType1', $message);
        $this->assertStringContainsString('DestinationType2', $message);
        $this->assertStringContainsString('destination class: DestinationClass', $message);
        $this->assertStringContainsString('property: propertyName', $message);
    }

    public function testNewMissingMapsExceptionWithContextDestinationClassOnly(): void
    {
        $exception = MapperException::newMissingMapsExceptionWithContext(
            'SourceType',
            ['DestinationType'],
            'DestinationClass',
            null
        );

        $message = $exception->getMessage();

        $this->assertStringContainsString('SourceType', $message);
        $this->assertStringContainsString('DestinationType', $message);
        $this->assertStringContainsString('destination class: DestinationClass', $message);
        $this->assertStringNotContainsString('property:', $message);
    }

    public function testNewMissingMapsExceptionWithContextPropertyOnly(): void
    {
        $exception = MapperException::newMissingMapsExceptionWithContext(
            'SourceType',
            ['DestinationType'],
            null,
            'propertyName'
        );

        $message = $exception->getMessage();

        $this->assertStringContainsString('SourceType', $message);
        $this->assertStringContainsString('DestinationType', $message);
        $this->assertStringContainsString('property: propertyName', $message);
        $this->assertStringNotContainsString('destination class:', $message);
    }

    public function testNewMissingMapsExceptionWithContextMinimal(): void
    {
        $exception = MapperException::newMissingMapsExceptionWithContext(
            'SourceType',
            ['DestinationType']
        );

        $message = $exception->getMessage();

        $this->assertStringContainsString('SourceType', $message);
        $this->assertStringContainsString('DestinationType', $message);
        $this->assertStringNotContainsString('destination class:', $message);
        $this->assertStringNotContainsString('property:', $message);
    }

    public function testMappingExceptionIncludesClassContext(): void
    {
        $this->expectException(MapperException::class);
        $this->expectExceptionCode(MapperException::MISSING_MAP);

        $autoMapper = new AutoMapper(new MapperConfiguration());
        $source = new TestSource();

        try {
            $autoMapper->map($source, TestDestination::class);
            $this->fail('Expected MapperException was not thrown');
        } catch (MapperException $e) {
            $message = $e->getMessage();
            $this->assertStringContainsString('TestSource', $message);
            $this->assertStringContainsString('TestDestination', $message);
            $this->assertStringContainsString('source class:', $message);
            $this->assertStringContainsString('destination class:', $message);
            $this->assertStringContainsString('mapping object', $message);
            throw $e; // Re-throw for the expectException assertion
        }
    }

    public function testMutateExceptionIncludesClassContext(): void
    {
        $this->expectException(MapperException::class);
        $this->expectExceptionCode(MapperException::MISSING_MAP);

        $autoMapper = new AutoMapper(new MapperConfiguration());
        $source = new TestSource();
        $destination = new TestDestination();

        try {
            $autoMapper->mutate($source, $destination);
            $this->fail('Expected MapperException was not thrown');
        } catch (MapperException $e) {
            $message = $e->getMessage();
            $this->assertStringContainsString('TestSource', $message);
            $this->assertStringContainsString('TestDestination', $message);
            $this->assertStringContainsString('source class:', $message);
            $this->assertStringContainsString('destination class:', $message);
            $this->assertStringContainsString('mutating object', $message);
            throw $e; // Re-throw for the expectException assertion
        }
    }

    public function testPropertyMappingExceptionIncludesPropertyContext(): void
    {
        $this->expectException(MapperException::class);
        $this->expectExceptionCode(MapperException::MISSING_MAP);

        $config = new MapperConfiguration(fn (Config $config) => $config
            ->createMap(TestSource::class, TestDestination::class)
            // Create a mapping but don't handle the complex property - this will fail
        );

        $autoMapper = $config->createMapper();
        $source = new TestSource();

        try {
            $autoMapper->map($source, TestDestination::class);
            $this->fail('Expected MapperException was not thrown');
        } catch (MapperException $e) {
            $message = $e->getMessage();
            $this->assertStringContainsString('TestComplexProperty', $message);
            $this->assertStringContainsString('TestAnotherComplexProperty', $message);
            $this->assertStringContainsString('destination class:', $message);
            $this->assertStringContainsString('TestDestination', $message);
            $this->assertStringContainsString('property: complex', $message);
            throw $e; // Re-throw for the expectException assertion
        }
    }
}

// Test classes for the exception tests
class TestSource
{
    public function __construct(
        public string $name = 'John',
        public TestComplexProperty $complex = new TestComplexProperty(),
    ) {
    }
}

class TestComplexProperty
{
    public function __construct(
        public string $value = 'test',
    ) {
    }
}

class TestDestination
{
    public string $name;
    public TestAnotherComplexProperty $complex;
}

class TestAnotherComplexProperty
{
    public string $value;
}
