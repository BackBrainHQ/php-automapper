<?php

declare(strict_types=1);

use Backbrain\Automapper\AutoMapper;
use Backbrain\Automapper\Bundle\Factory;
use Backbrain\Automapper\Contract\AutoMapperInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('backbrain_automapper_factory', Factory::class)
        ->call('setLogger', [service('logger')])
        ->call('setCacheItemPool', [service('cache.app')])
    ;

    $container->services()
        ->set('backbrain_automapper', AutoMapper::class)
        ->factory([service('backbrain_automapper_factory'), 'create'])
        ->alias(AutoMapperInterface::class, 'backbrain_automapper')
    ;
};
