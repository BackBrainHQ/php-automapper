<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Bundle\DependencyInjection;

use Backbrain\Automapper\Contract\Attributes\AsProfile;
use Backbrain\Automapper\Metadata\DirectoryMetadataProvider;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AutomapperExtension extends Extension implements ConfigurationInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../config'));
        $loader->load('services.php');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        $factoryDefinition = $container->findDefinition('backbrain_automapper_factory');

        $cacheAdapterServiceId = $config['metadata_cache_adapter'];
        $cacheExpressionLanguageServiceId = $config['expression_language'];
        $loggerServiceId = $config['logger'];

        $factoryDefinition->replaceArgument('$logger', new Reference($loggerServiceId));
        $factoryDefinition->replaceArgument('$cacheItemPool', new Reference($cacheAdapterServiceId));
        $factoryDefinition->replaceArgument('$expressionLanguage', new Reference($cacheExpressionLanguageServiceId));

        if (count($config['paths']) > 0) {
            foreach ((new DirectoryMetadataProvider())->scanPath(...$config['paths']) as $className) {
                $factoryDefinition->addMethodCall('addClass', [
                    '$className' => $className,
                ]);
            }
        }

        $container->registerAttributeForAutoconfiguration(AsProfile::class, static function (ChildDefinition $definition, AsProfile $attribute) {
            $definition->addTag('backbrain_automapper_profile');
        });
    }

    /**
     * @param array<mixed> $config
     */
    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return $this;
    }

    public function getAlias(): string
    {
        return 'backbrain_automapper';
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('backbrain_automapper');
        $rootNode = $treeBuilder->getRootNode();
        \assert($rootNode instanceof ArrayNodeDefinition);

        // @phpstan-ignore-next-line
        $rootNode->children()
            ->scalarNode('metadata_cache_adapter')->defaultValue('cache.system')->end()
            ->scalarNode('expression_language')->defaultValue('security.expression_language')->end()
            ->scalarNode('logger')->defaultValue('logger')->end()
            ->arrayNode('paths')
                ->scalarPrototype()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
