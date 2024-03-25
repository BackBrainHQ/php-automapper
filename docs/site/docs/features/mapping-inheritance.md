---
id: mapping-inheritance
title: Mapping Inheritance
sidebar_label: Mapping Inheritance
sidebar_position: 6
---

# Mapping Inheritance

Inheritance of base class configuration is not automatic, it requires explicit opt-in. 
You can specify the mapping to inherit from the base type configuration using the `include()` method, 
or within the derived type configuration using the `includeBase()` method.

:::info
When usiing `include()` or `includeBase()`, only the member configuration is inherited.
:::

:::tip
If you do not have any custom member configuration in the base type, inheritance is optional since
matching member names will still be mapped automatically.
:::

## Using `include()`

The `include()` method is used to include the mappings from one specific map into another. This is useful when you have two classes that have similar properties and you want to avoid duplicating the mapping configuration.

Here is an example:

```php
$config = new MapperConfiguration(fn (Config $config) => $config
    ->createMap(BaseClass::class, BaseDto::class)
        ->forMember('property1', fn (Options $opts) => $opts->mapFrom(fn (BaseClass $source) => $source->property1))
        ->include(DerivedClass::class, DerivedDto::class)
    ->createMap(DerivedClass::class, DerivedDto::class)
);
```

In this example, `DerivedClass` is a derived class of `BaseClass`, and `DerivedDto` is a derived class of `BaseDto`. The `include()` method is used to include the mappings from `BaseClass` to `BaseDto` into the map from `DerivedClass` to `DerivedDto`.

## Using `includeBase()`

The `includeBase()` method is used to include the mappings from a base map into the current map. This is useful when you have a base class and multiple derived classes, and you want to include the mappings of the base class into all the derived classes.

Here is an example:

```php
$config = new MapperConfiguration(fn (Config $config) => $config
    ->createMap(BaseClass::class, BaseDto::class)
        ->forMember('property1', fn (Options $opts) => $opts->mapFrom(fn (BaseClass $source) => $source->property1))
    ->createMap(DerivedClass1::class, DerivedDto1::class)
        ->includeBase(BaseClass::class, BaseDto::class)
    ->createMap(DerivedClass2::class, DerivedDto2::class)
        ->includeBase(BaseClass::class, BaseDto::class)
);
```

In this example, `DerivedClass1` and `DerivedClass2` are derived classes of `BaseClass`, and `DerivedDto1` and `DerivedDto2` are derived classes of `BaseDto`. The `includeBase()` method is used to include the mappings from `BaseClass` to `BaseDto` into the maps from `DerivedClass1` to `DerivedDto1` and from `DerivedClass2` to `DerivedDto2`.

## Alias

You can also use the `as()` method to specify an alias for the included map. This can be useful when you want to 
use the same map configuration for multiple types.

Here is an example:

```php
$config = new MapperConfiguration(fn (Config $config) => $config
    ->createMap('array', ArrayCollection::class)
    ->createMap('array', Collection::class)
        ->as(ArrayCollection::class)
```

You can use the `as()` method to specify that the map configuration from `ArrayCollection` 
should be used for `Collection`.

:::tip
This is especially useful because for interfaces. You do not need a TypeFactory for the `Collection` type, 
since it will use the `ArrayCollection` instantiation.
:::
