---
id: custom-value-resolvers
title: Custom Value Resolvers
sidebar_label: Custom Value Resolvers
sidebar_position: 2
---

# Custom Value Resolvers

Custom value resolvers in PHP AutoMapper provide a way to customize how a specific property or value is resolved during the mapping process. This can be particularly useful when the value of a property depends on some complex logic or external factors.

There are several reasons why you might want to use custom value resolvers:

- **Complex Logic**: If the value of a property depends on some complex logic, you can encapsulate this logic in a custom value resolver.
- **External Factors**: If the value of a property depends on external factors (like the current time, a value from a database, etc.), you can use a custom value resolver to handle this.
- **Code Organization**: Custom value resolvers allow you to keep your mapping configurations clean and focused on the mapping itself, while offloading complex value resolution to separate classes.

## How to Create a Custom Value Resolvers

### Anonymous Function

Use an anonymous function to define custom value resolution logic directly in your mapping configuration and
pass it to the `mapFrom()` options method.

Here's an example:
```php
use Backbrain\Automapper\Contract\Builder\Config;
use Backbrain\Automapper\MapperConfiguration;

$config = new MapperConfiguration(fn (Config $config) => $config
    ->createMap(SourceClass::class, DestinationClass::class)
        ->forMember(
            'passwordHash', 
            fn (Options $opts) => $opts->mapFrom(
                fn (SourceClass $source, ResolutionContextInterface $context) => hash('argon2id', $source->passwordPlain)
            )
        )
);
```

### The `ValueResolverInterface`

To create a custom value resolver, you need to create a class that implements the `ValueResolverInterface`. This interface has a single method `resolve` that you need to implement.

Here's an example:

```php
use Backbrain\Automapper\Contract\ValueResolverInterface;
use Backbrain\Automapper\Contract\ResolutionContextInterface;

class CustomValueResolver implements ValueResolverInterface
{
    public function resolve(object $source, ResolutionContextInterface $context): mixed
    {
        // Implement your custom value resolution logic here.
        // For example, let's assume we are calculating a value based on some properties of the source object.
        return hash('argon2id', $source->passwordPlain);
    }
}
```

In this example, `CustomValueResolver` is implementing `ValueResolverInterface`. The `resolve` method is responsible for calculating a value based on the properties of the source object.

To use this custom value resolver in your mapping configuration, you can use the `valueProvider` method:

```php
use Backbrain\Automapper\Contract\Builder\Config;
use Backbrain\Automapper\MapperConfiguration;

$config = new MapperConfiguration(fn (Config $config) => $config
    ->createMap(SourceClass::class, DestinationClass::class)
        ->forMember('passwordHash', fn (Options $opts) => $opts->mapFrom(new CustomValueResolver()))
);
```

In this configuration, AutoMapper will use `CustomValueResolver` to resolve the value of the `passwordHash` when mapping `SourceClass` to `DestinationClass`.