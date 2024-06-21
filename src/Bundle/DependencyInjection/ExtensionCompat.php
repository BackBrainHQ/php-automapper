<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Bundle\DependencyInjection;

if (class_exists('Symfony\Component\DependencyInjection\Extension\Extension')) {
    abstract class ExtensionCompat extends \Symfony\Component\DependencyInjection\Extension\Extension
    {
    }
} else {
    // The "Symfony\Component\HttpKernel\DependencyInjection\Extension" class is considered internal since Symfony 7.1,
    // to be deprecated in 8.1; use Symfony\Component\DependencyInjection\Extension\Extension instead.
    abstract class ExtensionCompat extends \Symfony\Component\HttpKernel\DependencyInjection\Extension
    {
    }
}
