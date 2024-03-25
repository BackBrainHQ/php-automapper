---
id: before-and-after-map-action
title: Before and After Map Action
sidebar_label: Before and After Map Action
sidebar_position: 5
---

# Before and After Map Action

The `beforeMap()` and `afterMap()` methods in PHP AutoMapper allow you to execute custom actions before 
and after the mapping process. These methods can be particularly useful when you need to perform some 
additional processing or validation that is not directly related to the mapping itself.


## The `MappingActionInterface`

The `MappingActionInterface` is an interface in PHP AutoMapper that allows you to define custom actions to
be executed before or after the mapping process. This can be particularly useful when you need to perform
some additional operations on the source or destination objects, such as data validation, transformation, or logging.

To use the `MappingActionInterface`, you need to create a class that implements this interface. The
interface has a single method `process` that you need to implement. This method receives three
parameters: the source object, the destination object, and the resolution context.

Here's an example of how to implement the `MappingActionInterface`:

```php
use Backbrain\Automapper\Contract\MappingActionInterface;
use Backbrain\Automapper\Contract\ResolutionContextInterface;

class CustomMappingAction implements MappingActionInterface
{
    public function process(mixed $source, mixed $destination, ResolutionContextInterface $context): void
    {
        // Implement your custom action here.
        // This is a simple example that logs the source object.
        error_log(print_r($source, true));
    }
}
```

In this example, `CustomMappingAction` is implementing `MappingActionInterface`. The `process` method is
responsible for logging the source object.

To use your custom mapping action, you need to specify it in your `MapperConfiguration` using
the `beforeMap` or `afterMap` methods:

```php
use Backbrain\Automapper\Contract\Builder\Config;
use Backbrain\Automapper\MapperConfiguration;

$config = new MapperConfiguration(fn (Config $config) => $config
    ->createMap(SourceClass::class, DestinationClass::class)
        ->beforeMap(new CustomMappingAction())
);
```

In this configuration, AutoMapper will execute `CustomMappingAction` before mapping `SourceClass`
to `DestinationClass`. If you want to execute the action after the mapping, you can use the `afterMap`
method instead.

## Anonymous Functions
Here's how you can use these methods:

```php
use Backbrain\Automapper\Contract\Builder\Config;
use Backbrain\Automapper\Contract\ResolutionContextInterface;
use Backbrain\Automapper\MapperConfiguration;

$config = new MapperConfiguration(fn (Config $config) => $config
    ->createMap(SourceClass::class, DestinationClass::class)
        ->beforeMap(function(SourceClass $source, DestinationClass $destination, ResolutionContextInterface $context) {
            // This code will be executed before the mapping.
            // You can access the source and destination objects, and the current context.
            // For example, let's log the start of the mapping process:
            error_log(print_r($source, true));
        })
        ->afterMap(function(SourceClass $source, DestinationClass $destination, ResolutionContextInterface $context) {
            // This code will be executed after the mapping.
            // You can access the source and destination objects, and the current context.
            // For example, let's log the end of the mapping process:
            error_log(print_r($destination, true));
        })
);
```

In this example, we're using `beforeMap()` to log the start of the mapping process, and `afterMap()` to log 
the end of the mapping process. The `ResolutionContextInterface` provides access to the current context.
