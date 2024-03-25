---
id: null-substitution
title: Null Substitution
sidebar_label: Null Substitution
sidebar_position: 4
---

# Null Substitution

The nullSubstitute() method in PHP AutoMapper lets you define a replacement value to be used when the 
value of the source property is null. This is especially useful when you want to guarantee that 
specific properties in the destination object are never null.

Here's an example:
```php
use Backbrain\Automapper\Contract\Builder\Config;
use Backbrain\Automapper\MapperConfiguration;

$config = new MapperConfiguration(fn (Config $config) => $config
    ->createMap(SourceClass::class, DestinationClass::class)
        ->forMember('property', fn (Options $opts) => $opts->nullSubstitute('default value'))
);
```

In this example, if the `property` in the `SourceClass` is `null`, AutoMapper will use the string `'default value'` instead when mapping to `DestinationClass`.
