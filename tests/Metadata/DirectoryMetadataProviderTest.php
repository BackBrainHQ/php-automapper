<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Metadata;

use Backbrain\Automapper\Metadata\DirectoryMetadataProvider;
use Backbrain\Automapper\Tests\Fixtures\Metadata\Bar\Bar;
use Backbrain\Automapper\Tests\Fixtures\Metadata\Foo;
use PHPUnit\Framework\TestCase;

class DirectoryMetadataProviderTest extends TestCase
{
    public function testScanPath(): void
    {
        $classes = (new DirectoryMetadataProvider())->scanPath(__DIR__.'/../Fixtures/Metadata');

        $this->assertContains(Foo::class, $classes, 'Class Foo not found in the directory.');
        $this->assertContains(Bar::class, $classes, 'Class Bar not found in the directory.');
    }
}
