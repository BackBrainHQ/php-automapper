<?php

declare(strict_types=1);

namespace Backbrain\Automapper;

use Backbrain\Automapper\Bundle\DependencyInjection\AutomapperCompilerPass;
use Backbrain\Automapper\Bundle\DependencyInjection\AutomapperExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BackbrainAutomapperBundle extends Bundle
{
    protected function createContainerExtension(): ?ExtensionInterface
    {
        return new AutomapperExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AutomapperCompilerPass());
    }
}
