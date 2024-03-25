---
id: construction
title: Construction
sidebar_label: Construction
sidebar_position: 5
---

# Construction

In the AutoMapper library, you can configure the construction or instantiation of objects using a `TypeFactory`. This is particularly useful when dealing with interfaces or any objects that cannot be instantiated directly.

Here's a step-by-step guide on how to do this:

1. First, you need to define a `TypeFactory` for the specific type. A `TypeFactory`
   is either a callable (function or method) that takes the source object and  a `ResolutionContextInterface`  
   as arguments and returns an instance of the target type or a class that implements the `TypeFactoryInterface`.

2. In your `MapperConfiguration`, you can use the `constructUsing()` method to specify the `TypeFactory` for a
   particular mapping. 

:::info
`constructUsing()` is mandatory for interfaces and any object that cannot be instantiated directly due to the lack of a public constructor,
or abstract classes, or any other reason.
:::

:::warning
A `TypeFactory` is required to return an instance of the target type. If the `TypeFactory` returns an instance of a different type, AutoMapper will throw an exception.
:::

## Using a Callable
Here's an example of how you can do this:

```php
use Backbrain\Automapper\Contract\Builder\Config;
use Backbrain\Automapper\MapperConfiguration;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

$config = new MapperConfiguration(fn (Config $config) => $config
    ->createMap(User::class, UserDto::class)
    ->createMap(Address::class, AddressDto::class)
    ->createMap('array', Collection::class)
        ->constructUsing(fn (array $source, ResolutionContextInterface $context): object => new ArrayCollection())

$autoMapper = $config->createMapper();
```

Remember, the `TypeFactory` is mandatory for interfaces and all objects that cannot be instantiated directly.

## Using a Class

If you prefer to use a class that implements the `TypeFactoryInterface`, you can create a class that implements the `TypeFactoryInterface` and then pass it to the `constructUsing()` method.

Here's an example of how you can do this:

```php
use Backbrain\Automapper\Contract\ResolutionContextInterface;
use Backbrain\Automapper\Contract\TypeFactoryInterface;
use Backbrain\Automapper\Contract\Builder\Config;
use Backbrain\Automapper\MapperConfiguration;

class ExampleTypeFactory implements TypeFactoryInterface
{
    public function create(mixed $source, ResolutionContextInterface $context): object
    {
        // Here you can implement your logic to create a new instance of a class.
        // For example, let's assume we are creating an instance of a class named "ExampleClass".
        // You can use the $source and $context parameters to customize the creation process.

        // Set properties or constructor arguments for $exampleClass based on $source and $context
        $exampleClass = new ExampleClass();

        return $exampleClass;
    }
}
```

In this example, `ExampleTypeFactory` is implementing `TypeFactoryInterface`. The `create` method is responsible for creating and returning a new instance of a class. The `create` method receives two parameters: `$source` and `$context`. You can use these parameters to customize the creation process of the new instance. In this example, we are creating an instance of a hypothetical class named `ExampleClass`.