<?php

declare(strict_types=1);

use Backbrain\Automapper\AutoMapper;
use Backbrain\Automapper\Context\ResolutionContextProvider;
use Backbrain\Automapper\Contract\AutoMapperInterface;
use Backbrain\Automapper\Contract\ResolutionContextProviderInterface;
use Backbrain\Automapper\Factory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('backbrain_automapper_factory', Factory::class)
        ->args([
            '$expressionLanguage' => service('security.expression_language'),
            '$logger' => service('logger'),
            '$cacheItemPool' => service('cache.system'),
            '$resolutionContextProvider' => service('backbrain_automapper_resolution_context_provider'),
            '$container' => service('service_container'),
        ]);

    $container->services()
        ->set('backbrain_automapper', AutoMapper::class)
        ->factory([service('backbrain_automapper_factory'), 'create'])
        ->alias(AutoMapperInterface::class, 'backbrain_automapper');

    $container->services()
        ->set('backbrain_automapper_resolution_context_provider', ResolutionContextProvider::class)
        ->alias(ResolutionContextProviderInterface::class, 'backbrain_automapper_resolution_context_provider');
};
