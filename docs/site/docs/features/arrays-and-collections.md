---
id: arrays-and-collections
title: Arrays and Collections
sidebar_label: Arrays and Collections
sidebar_position: 4
---

# Arrays And Collections


## Arrays
Iterable type properties allow you to map arrays or collections of objects from a source object to a destination object. For example, consider the following classes:

```php
class User {
    public string $name;
    /** @var Address[] */
    public array $addresses;
}

class Address {
    public string $street;
    public string $city;
}

class UserDto {
    public string $name;
    /** @var AddressDto[] */
    public array $addresses;
}

class AddressDto {
    public string $street;
    public string $city;
}
```

In this case, `User` has an array of `Address` objects. If you want to map `User` to `UserDto`, and also map the array of `Address` to `AddressDto`, you can do so with AutoMapper:

```php
$config = new MapperConfiguration(fn (Config $config) => $config
    ->createMap(User::class, UserDto::class)
    ->createMap(Address::class, AddressDto::class)
);
```

In this configuration, AutoMapper will automatically map the array of `Address` objects when mapping `User` to `UserDto`.

## Collections

This is not limited to arrays. You may use any destination type that implements the `ArrayAccess` interface. 

If you are using Doctrine Collections, you can use the `Collection` type hint to map collections of objects. 
For example, consider the following classes:
```php
class UserDto {
    /** @var Collection<int, AddressDto>  */
    public \Doctrine\Common\Collections\Collection $addresses;
}
```

:::info

Since `Collection` is an interface, you need to provide the concrete implementation of the collection.
For example, you can use the `ArrayCollection` class as the concrete implementation of the `Collection` interface.

```php
$config = new MapperConfiguration(fn (Config $config) => $config
    ->createMap(User::class, UserDto::class)
    ->createMap(Address::class, AddressDto::class)
    ->createMap('array', Collection::class)
        ->constructUsing(fn (array $source) => new ArrayCollection())
```
:::

## Mapping Iterable Source Objects 
If the source itself is an iterable object, you can use the `mapIterable()` function to map the iterable object to the destination object. 
For example, consider the following classes:

```php
// Create the AutoMapper using the configuration
$autoMapper = $config->createMapper();

// Use the mapIterable() function to map the User object to UserDto
$addressDTOs = $autoMapper->mapIterable([new Address(), new Address()], 'AddressDto[]');
```

In this example, `User` is the source object and `UserDto` is the destination object. The `mapIterable()` function is used to map the array of `Address` instances to an array of `AddressDto` instances.


