---
id: conditional-mapping
title: Conditional Mapping
sidebar_label: Conditional Mapping
sidebar_position: 3
---

# Conditional Mapping

In AutoMapper, you can specify conditions for properties that need to be fulfilled before the mapping of those properties takes place.

```php
use Backbrain\Automapper\Contract\Builder\Config;
use Backbrain\Automapper\MapperConfiguration;

$config = new MapperConfiguration(fn (Config $config) => $config
    ->createMap(SourceClass::class, DestinationClass::class)
        ->forMember('property', fn (Options $opts) => $opts
            ->condition(fn (SourceClass $source) => $source->property !== null)
        )
);
```

In this example, the `property` of the `SourceClass` is mapped to the `property` of the `DestinationClass` only if the `property` of the `SourceClass` is not `null`. If the `property` of the `SourceClass` is `null`, the mapping is skipped.