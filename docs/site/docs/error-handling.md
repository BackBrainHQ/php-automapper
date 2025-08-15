---
id: error-handling
title: Error handling and diagnostics
sidebar_label: Error handling
sidebar_position: 50
---

This page explains how PHP AutoMapper reports errors, how to interpret them, and how to debug mapping issues effectively.

## MapperException codes

The library throws Backbrain\Automapper\Exceptions\MapperException with well-defined error codes:

- MISSING_MAP (1000)
  - Cause: No mapping defined between the source and destination types.
  - Fix: Define a map for the source/destination pair (createMap). If using iterable mapping, ensure you have a map for the element types.
- CLASS_NOT_FOUND (1001)
  - Cause: Destination class does not exist or cannot be autoloaded.
  - Fix: Verify the class name string and Composer autoloading.
- UNEXPECTED_TYPE (1002)
  - Cause: A type converter/factory expected a different input type, or instantiation received an unexpected value.
  - Fix: Check custom converters/factories and member mappings for correct types.
- COLLECTION_NOT_WRITEABLE (1003)
  - Cause: Destination collection is not writeable (does not implement ArrayAccess).
  - Fix: Use an ArrayAccess-compatible collection or a different destination type.
- CIRCULAR_DEPENDENCY (1004)
  - Cause: A circular dependency is detected in the mapping stack.
  - Fix: Revisit your profile design; break the cycle or use custom resolvers.
- ILLEGAL_TYPE_EXPRESSION (1005)
  - Cause: Destination type expression is invalid.
  - Rules: Only letters, digits, `_|\\<>[] ,` are accepted; spaces within generic expressions are not allowed.
  - Examples:
    - Valid: `Array<\App\Dto\ProfileDTO>`, `list<\App\Dto\ItemDTO|int>`
    - Invalid: `Array< App\Dto\ProfileDTO >` (contains spaces)
- INSTANTIATION_FAILED (1006)
  - Cause: Destination type cannot be instantiated (non-instantiable or missing factory).
  - Fix: Ensure the destination has a public no-arg constructor, or configure a factory via constructUsing().

## Context-aware messages

Every MapperException message includes a context suffix when available, for example:

```
No mapping found for source type "App\Dto\Account" to destination type "App\Dto\Profile" Context: path=(root) depth=0 source=App\Dto\Account dest=App\Dto\Profile
```

Depending on the failure, the context may also include the applied map id (e.g., `map=App\Src => App\Dest`) to help identify which map was being used.

## Debugging tips

- Enable logging: AutoMapper uses PSR-3 logging internally. Set a logger to see debug/warning messages about property readability/writability and member conditions.

```php
use Psr\Log\LoggerInterface;

$logger = /* your PSR-3 logger */;
$autoMapper->setLogger($logger);
```

- Check member access: The mapper logs when a source property is not readable or a destination property is not writable using Symfony PropertyAccess.
- Verify type expressions when using mapIterable: Expressions must be valid and whitespace-free (see rules above).
- Inspect exception codes: Catch MapperException and switch on `$e->getCode()` to tailor handling.

```php
try {
    $dest = $autoMapper->map($src, App\Dto\ProfileDTO::class);
} catch (Backbrain\Automapper\Exceptions\MapperException $e) {
    switch ($e->getCode()) {
        case Backbrain\Automapper\Exceptions\MapperException::MISSING_MAP:
            // register/create the missing map
            break;
        case Backbrain\Automapper\Exceptions\MapperException::ILLEGAL_TYPE_EXPRESSION:
            // fix the type expression passed to mapIterable
            break;
        // ... other cases
    }
}
```

## Related features

- Construction and factories: see Features → Construction.
- Arrays and collections: see Features → Arrays and Collections.
- Naming conventions: see Features → Naming Conventions.
