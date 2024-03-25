---
id: profiles
title: Profiles
sidebar_label: Profiles
sidebar_position: 1
---

# Profiles

In PHP AutoMapper, a Profile is a class where you can group related mappings. Using Profiles can help you manage your mappings in a more organized and maintainable way.

## Why Use Profiles?

There are several reasons why you might want to use Profiles in AutoMapper:

- **Organization**: Profiles allow you to group related mappings together. This can make your code easier to understand and maintain.
- **Reuse**: If you have mappings that are used in multiple places, you can define these mappings in a Profile and then reuse that Profile wherever needed.
- **Extensibility**: Profiles can be easily extended to add new mappings or modify existing ones.

## How to Use Profiles

To use a Profile, you need to create a class that extends the `Profile` class. In this class, you can define your mappings in the constructor. Here's an example:

```php
use Backbrain\Automapper\Contract\ResolutionContextInterface;
use Backbrain\Automapper\Profile;
use Backbrain\Automapper\Helper\Value;

class ScalarToStringProfile extends Profile
{
    public function __construct()
    {
        $this
            ->createMap('int', 'string')
                ->convertUsing(fn (mixed $source, ResolutionContextInterface $context): string => sprintf('%d', Value::asInt($source)))
            ->createMap('float', 'string')
                ->convertUsing(fn (mixed $source, ResolutionContextInterface $context): string => sprintf('%f', Value::asFloat($source)))
            ->createMap('bool', 'string')
                ->convertUsing(fn (mixed $source, ResolutionContextInterface $context): string => Value::asBool($source) ? 'true' : 'false');
    }
}
```

In this example, we're creating a `ScalarToStringProfile` that defines mappings from scalar types (`int`, `float`, `bool`) to `string`.

To use this Profile, you need to add it to your `MapperConfiguration`:

```php
$config = new MapperConfiguration(fn (Config $config) => $config
    ->addProfile(new ScalarToStringProfile())
);
```

Now, the mappings defined in `ScalarToStringProfile` will be available to the AutoMapper instance created from this configuration.
