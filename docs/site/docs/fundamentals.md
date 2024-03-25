---
id: fundamentals
title: Fundamentals
sidebar_label: Fundamentals
sidebar_position: 2
---

# Fundamentals

## Types

In PHP AutoMapper, the types of class properties play a crucial role in how mapping is performed. 
The types are determined using the [Symfony PropertyInfo](https://symfony.com/doc/current/components/property_info.html) component. This means you can use PHP 
type hints or PHPDoc type hints to define the types of your properties.

Here's an example:

```php
class SourceClass {
    /**
     * @var string[]
     */
    private array $titles;
}
```

In the above example, the titles member is a typed array of strings. 

### Default Mapping

By default, AutoMapper will automatically map the values if the source and destination types match.
For instance, if a member in the source class is of type `string` and the corresponding member 
in the destination class is also of type `string`, AutoMapper will automatically map the value without 
further configuration.

### Customizing Type Conversion

In cases where the source and destination types do not match (e.g., mapping a `string` to an `int`),
a custom mapping is required. This can be achieved using the `forMember` method and providing a custom 
mapping function.

Here's an example:

```php
class SourceClass {
    public string $age;
}

class DestinationClass {
    public int $age;
}

$config = new MapperConfiguration(fn (Config $config) => $config
    ->createMap(SourceClass::class, DestinationClass::class)
    ->createMap('string', 'int')
    ->convertUsing(fn (string $source, ResolutionContextInterface $context): int => intval($source))
);
```

In this example, we're mapping the `age` member of `SourceClass` (which is a `string`) to the `age`
member of `DestinationClass` (which is an `int`). The custom mapping function converts the string to an 
integer before mapping.

## Members

In PHP AutoMapper, properties of classes are referred to as "members". These members are the key elements 
that are mapped from source to destination classes.

PHP AutoMapper uses the [Symfony PropertyAccess](https://symfony.com/doc/current/components/property_access.html) component to read and write member values. This component
allows AutoMapper to access public, protected, and private properties using getter and setter methods.
This means that even if your class properties are not public, AutoMapper can still map them as long as
there are appropriate getter and setter methods available.

### Default Mapping

By default, AutoMapper maps members with matching names between the source and destination classes. This 
means if your source class has a property named `title`, and your destination class also has a property 
named `title`, AutoMapper will automatically map the value of `title` from the source to the destination.

```php
class SourceClass {
    public string $title;
}

class DestinationClass {
    public string $title;
}

$config = new MapperConfiguration(fn (Config $config) => $config
    ->createMap(SourceClass::class, DestinationClass::class)
);

$autoMapper = $config->createMapper();
$destination = $autoMapper->map(new SourceClass(), DestinationClass::class);
```

In the above example, the `title` member of `SourceClass` will be mapped to the `title` member 
of `DestinationClass`.

### Customizing Member Mapping

You can customize how individual members are mapped using the `forMember` method. The mapping configuration 
is defined on the destination type. Here's an example:

```php
$config = new MapperConfiguration(fn (Config $config) => $config
    ->createMap(SourceClass::class, DestinationClass::class)
    ->forMember(
        'title',
        fn (Options $opts) => $opts->mapFrom(
            fn (SourceClass $source) => $source->title . ' custom'
        )
    )
);
```

In this example, we're appending the string ' custom' to the `title` member of `SourceClass` when it's
mapped to `DestinationClass`.


