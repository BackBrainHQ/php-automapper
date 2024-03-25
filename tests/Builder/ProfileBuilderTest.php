<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Tests\Builder;

use Backbrain\Automapper\Builder\DefaultConfig;
use Backbrain\Automapper\Context\ResolutionContext;
use Backbrain\Automapper\Profile;
use PHPUnit\Framework\TestCase;

class ProfileBuilderTest extends TestCase
{
    public function testProfileBuilderShouldCreateMap()
    {
        $profileBuilder = new DefaultConfig();

        $mapBuilder = $profileBuilder->createMap('SourceClass', 'DestinationClass');

        $this->assertNotNull($mapBuilder);
    }

    public function testProfileBuilderShouldAddProfile()
    {
        $profileBuilder = new DefaultConfig();
        $this->assertCount(0, $profileBuilder->getMaps());

        $profileBuilder->addProfile(new class() extends Profile {
            public function __construct()
            {
                $this->createMap('SourceClass', 'DestinationClass');
            }
        });

        $this->assertCount(1, $profileBuilder->getMaps());
    }

    public function testProfileBuilderShouldDeduplicateMaps()
    {
        $profileBuilder = new DefaultConfig();

        $profileBuilder->createMap('SourceClass', 'DestinationClass');
        $profileBuilder->createMap('SourceClass', 'DestinationClass');

        $this->assertCount(1, $profileBuilder->getMaps());
    }

    public function testProfileBuilderShouldDeduplicateMapsAndLastOneWins()
    {
        $profileBuilder = new DefaultConfig();

        $profileBuilder->createMap('SourceClass', 'DestinationClass')->convertUsing(function ($source) {
            return $source.'_first';
        });

        $profileBuilder->createMap('SourceClass', 'DestinationClass')->convertUsing(function ($source) {
            return $source.'_last';
        });

        $maps = $profileBuilder->getMaps();
        $this->assertCount(1, $maps);

        $map = reset($maps);
        $this->assertEquals('test_last', $map->getTypeConverter()->convert('test', new ResolutionContext()));
    }
}
