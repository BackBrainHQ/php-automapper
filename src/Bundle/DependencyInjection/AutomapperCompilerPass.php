<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Bundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AutomapperCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $factoryDefinition = $container->findDefinition('backbrain_automapper_factory');

        foreach ($container->findTaggedServiceIds('backbrain_automapper_profile') as $id => $tags) {
            $factoryDefinition->addMethodCall('addProfile', [new Reference($id)]);
        }

        foreach ($container->findTaggedServiceIds('backbrain_automapper_model') as $id => $tags) {
            $def = $container->findDefinition($id);

            foreach ($tags as $tag) {
                $factoryDefinition->addMethodCall('addModel', [
                    '$dest' => $def->getClass(),
                    '$source' => $tag['source'] ?? null,
                    '$include' => $tag['include'] ?? null,
                ]);
            }
        }
    }
}
