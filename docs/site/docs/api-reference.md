---
id: api-reference
title: API reference
sidebar_label: API reference
sidebar_position: 40
---

This page documents the primary public methods of Backbrain\Automapper\AutoMapper.

See also: Features → Arrays and Collections; Error handling.

## map(source, destinationType): object

Map a source object to a new destination object of the given type.

Signature:

```php
/**
 * @template T of object
 * @param class-string<T> $destinationType
 * @return T
 */
public function map(object $source, string $destinationType): object;
```

Usage:

```php
$profile = $autoMapper->map($account, App\Dto\ProfileDTO::class);
```

Notes:
- Requires a mapping between the concrete source class and destination class.
- Throws MapperException (e.g., MISSING_MAP, CLASS_NOT_FOUND, INSTANTIATION_FAILED).

## mapIterable(source, destinationTypeExpr): iterable

Map an iterable (array or Traversable) to a destination collection type.

Signature:

```php
public function mapIterable(iterable $source, string $destinationType): iterable;
```

Destination type must be a valid type expression representing a collection, e.g.:
- `Array<\\App\\Dto\\AddressDTO>`
- `list<\\App\\Dto\\ItemDTO|int>`
- `\\App\\Collection\\AddressCollection` (must be writeable via ArrayAccess)

Example:

```php
$addresses = $autoMapper->mapIterable([new Address(), new Address()], 'Array<\\App\\Dto\\AddressDTO>');
```

Notes:
- The expression is parsed using PHPStan type utilities. Illegal characters or embedded spaces will cause ILLEGAL_TYPE_EXPRESSION.
- A map must exist for the element types; otherwise MISSING_MAP is thrown.
- If the target collection type does not implement ArrayAccess, COLLECTION_NOT_WRITEABLE is thrown.

## mutate(source, destination): void

Mutate an existing destination object in place by mapping members from source.

Signature:

```php
public function mutate(object $source, object $destination): void;
```

Example:

```php
$autoMapper->mutate($partialUpdate, $existingProfile);
```

Notes:
- Useful for update flows where the destination already exists (e.g., ORM-managed entity).
- Requires the same map used by `map()` for the source/destination classes.

## Type expression rules (for mapIterable)

The following regex is enforced to validate expressions:

```
/^[\\\\a-zA-Z0-9_|<>\[\],]+$/
```

Practical guidance:
- Do not include spaces inside generic brackets (e.g., `Array<\\App\\Dto\\X>` not `Array< App\\Dto\\X >`).
- Use fully qualified class names when possible.

## Diagnostics and logging

- Exceptions: See Error handling for codes and fixes.
- Logging: AutoMapper emits PSR-3 debug/warning logs for property readability/writability and condition checks. You can set your logger via `setLogger()`.
