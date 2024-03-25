---
id: configuration
title: Configuration
sidebar_label: Configuration
sidebar_position: 3
---

# Configuration

Configuring PHP AutoMapper involves creating a `MapperConfiguration` instance and defining mappings between source and destination classes. Here's a basic example:

```php
<?php

use Backbrain\Automapper\Contract\Builder\Options;
use Backbrain\Automapper\Contract\Builder\Config;
use Backbrain\Automapper\MapperConfiguration;

class SourceClass {
    public string $property;
}

class DestinationClass {
    public string $property;
}

$config = new MapperConfiguration(fn (Config $config) => $config
    ->createMap(SourceClass::class, DestinationClass::class)
);

$autoMapper = $config->createMapper();
```

In this example, we're creating a mapping between `SourceClass` and `DestinationClass`. The `createMap` method is used to define this mapping.

## Customizing Property Mapping

You can customize how individual properties are mapped using the `forMember` method. Here's an example:

```php
$config = new MapperConfiguration(fn (Config $config) => $config
    ->createMap(SourceClass::class, DestinationClass::class)
    ->forMember(
        'property',
        fn (Options $opts) => $opts->mapFrom(
            fn (SourceClass $source) => $source->property . ' custom'
        )
    )
);
```

In this example, we're appending the string ' custom' to the `property` of `SourceClass` when it's mapped to `DestinationClass`.

## Creating the AutoMapper Instance

Once you've defined your mappings, you can create an AutoMapper instance using the `createMapper` method:

```php
$autoMapper = $config->createMapper();
```

This `AutoMapper` instance can then be used to map objects as per the defined mappings.


