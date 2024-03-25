---
id: nested-mappings
title: Nested Mappings
sidebar_label: Nested Mappings
sidebar_position: 3
---

# Nested Mappings

Nested mappings allow you to map nested objects from a source object to a destination object. 
For example, consider the following classes:

```php
class User {
    public string $name;
    public Address $address;
}

class Address {
    public string $street;
    public string $city;
}

class UserDto {
    public string $name;
    public AddressDto $address;
}

class AddressDto {
    public string $street;
    public string $city;
}
```

In this case, `User` has a nested `Address` object. If you want to map `User` to `UserDto`, and also map 
the nested `Address` to `AddressDto`:

```php
$config = new MapperConfiguration(fn (Config $config) => $config
    ->createMap(User::class, UserDto::class)
    ->createMap(Address::class, AddressDto::class)
);
```

In this configuration, AutoMapper will automatically map the nested `Address` object when mapping `User` to `UserDto`.
