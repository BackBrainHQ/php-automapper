---
id: custom-type-converters
title: Custom Type Converters
sidebar_label: Custom Type Converters
sidebar_position: 1
---

# Custom Type Converters

Custom Type Converters in PHP AutoMapper allow you to define custom conversion logic for specific types. This can be particularly useful in scenarios where the default conversion logic does not meet your requirements or when you need to perform complex transformations.

For example, you might have a `DateTime` object in your source class that you want to convert to a formatted string in your destination class. Or, you might have a complex object that you want to flatten into a simple string or array in your destination class.

By implementing the `TypeConverterInterface`, you can define exactly how these conversions should be performed.

Using custom type converters can provide several benefits:

- **Flexibility**: You can define exactly how conversions should be performed, giving you full control over the mapping process.
- **Reusability**: Once defined, a custom type converter can be reused across multiple mappings.
- **Complex transformations**: Custom type converters allow you to perform complex transformations that would not be possible with the default conversion logic.

## How to Create a Custom Type Converter

To create a custom type converter, you need to create a class that implements the `TypeConverterInterface`. This interface has a single method `convert` that you need to implement.

Here's an example:

```php
use Backbrain\Automapper\Contract\ResolutionContextInterface;
use Backbrain\Automapper\Contract\TypeConverterInterface;

class CustomDateTimeToStringConverter implements TypeConverterInterface
{
    public function convert(mixed $source, ResolutionContextInterface $context): mixed
    {
        // Implement your custom conversion logic here.
        // This is a simple example that converts a DateTime object to a formatted string.
        if ($source instanceof \DateTime) {
            return $source->format('Y-m-d H:i:s');
        }

        return $source;
    }
}
```

In this example, `CustomDateTimeToStringConverter` is implementing `TypeConverterInterface`. The `convert` method is responsible for converting a `DateTime` object to a formatted string.

To use your custom type converter, you need to specify it in your `MapperConfiguration`:

```php
$config = new MapperConfiguration(fn (Config $config) => $config
    ->createMap(\DateTime::class, 'string')
        ->convertUsing(new CustomDateTimeToStringConverter())
);
```

In this configuration, AutoMapper will use `CustomDateTimeToStringConverter` to convert `DateTime` objects to strings.
