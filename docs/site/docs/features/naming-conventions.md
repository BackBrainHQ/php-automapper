---
id: naming-conventions
title: Naming Conventions
sidebar_label: Naming Conventions
sidebar_position: 2
---

# Naming Conventions

Naming conventions in PHP AutoMapper are instrumental in establishing a clear and consistent mapping 
between the properties of source and destination classes. AutoMapper comes equipped with a variety of 
pre-defined (built-in) naming conventions, and also offers the flexibility to create custom naming 
conventions tailored to your needs.

## Usage

To use a naming convention, you need to specify it in your `MapperConfiguration`. Here's an example:

```php
use Backbrain\Automapper\Converter\Naming\CamelCaseNamingConvention;
use Backbrain\Automapper\Converter\Naming\SnakeCaseNamingConvention;

$config = new MapperConfiguration(fn (Config $config) => $config
    ->createMap(SourceClass::class, DestinationClass::class)
    ->destinationMemberNamingConvention(SnakeCaseNamingConvention::class)
    ->sourceMemberNamingConvention(CamelCaseNamingConvention::class)
);
```

In this example, the source class properties are expected to be in camel case, and they will be mapped to 
the destination class properties in snake case.

## Custom Naming Conventions

To create a custom naming convention, you need to create a class that implements 
the `NamingConventionInterface`. This interface has a single method `translate` that you need to implement. 
Here's an example:

```php
use Backbrain\Automapper\Contract\NamingConventionInterface;

class CustomNamingConvention implements NamingConventionInterface
{
    public function translate(string $name): string
    {
        // Implement your custom naming convention here.
        // This is a simple example that replaces underscores with dashes.
        return str_replace('_', '-', $name);
    }
}
```

You can then use your custom naming convention in the same way as the built-in ones:

```php
$config = new MapperConfiguration(fn (Config $config) => $config
    ->createMap(SourceClass::class, DestinationClass::class)
    ->destinationMemberNamingConvention(CustomNamingConvention::class)
);
```

## Built-in

PHP AutoMapper comes with the following built-in naming conventions:

- `CamelCaseNamingConvention`: Converts a string to camel case. For example, `hello_world` becomes `helloWorld`.
- `PascalCaseNamingConvention`: Converts a string to Pascal case. For example, `hello_world` becomes `HelloWorld`.
- `SnakeCaseNamingConvention`: Converts a string to snake case. For example, `HelloWorld` becomes `hello_world`.
- `MacroCaseNamingConvention`: Converts a string to macro case. For example, `hello_world` becomes `HELLO_WORLD`.
- `AdaCaseNamingConvention`: Converts a string to Ada case. For example, `hello_world` becomes `Hello_World`.
