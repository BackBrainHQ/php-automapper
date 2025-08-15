---
id: symfony
title: Symfony Integration
sidebar_label: Symfony
sidebar_position: 6
---

# Symfony Integration

This guide shows how to use PHP AutoMapper in a Symfony application: installation, configuration, dependency injection, and profile registration.

## Installation

Install the library (and, if not already present, the FrameworkBundle for bundle integration):

```bash
composer require backbrain/php-automapper
# Symfony apps usually already include framework-bundle; if not:
# composer require symfony/framework-bundle
```

If Symfony Flex provides a recipe, the bundle is auto-registered. Otherwise, add it manually in `config/bundles.php`:

```php
return [
    // ...
    Backbrain\Automapper\BackbrainAutomapperBundle::class => ['all' => true],
];
```

## Configuration

AutoMapper works out of the box with sensible defaults. To customize, create `config/packages/backbrain_automapper.yaml`:

```yaml
backbrain_automapper:
  # PSR-6 CacheItemPoolInterface service id used for metadata caching (default: cache.system)
  metadata_cache_adapter: cache.system

  # Symfony ExpressionLanguage service id used for expression-based mappings (default: security.expression_language)
  expression_language: security.expression_language

  # PSR-3 LoggerInterface service id (default: logger)
  logger: logger

  # Optional: scan these directories recursively and register discovered classes with the factory
  paths:
    - '%kernel.project_dir%/src/Dto'
    - '%kernel.project_dir%/src/Domain'
```

PHP config alternative (`config/packages/backbrain_automapper.php`):

```php
<?php
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('backbrain_automapper', [
        'metadata_cache_adapter' => 'cache.system',
        'expression_language' => 'security.expression_language',
        'logger' => 'logger',
        'paths' => [
            '%kernel.project_dir%/src/Dto',
        ],
    ]);
};
```

Note: XML config is not currently provided via an XSD in this package; prefer YAML or PHP formats.

## Usage (Dependency Injection)

Type-hint the AutoMapper interface to use autowiring in your services or controllers:

```php
use Backbrain\Automapper\Contract\AutoMapperInterface;

final class ApiService
{
    public function __construct(private AutoMapperInterface $autoMapper) {}
}
```

Alternatively, inject by service id:

```yaml
services:
  App\Service\ApiService:
    arguments:
      $autoMapper: '@backbrain_automapper'
```

### Service IDs and Aliases

- `backbrain_automapper` → Backbrain\Automapper\AutoMapper (factory-created). Also aliased to `Backbrain\Automapper\Contract\AutoMapperInterface`.
- `backbrain_automapper_factory` → Backbrain\Automapper\Factory
- `backbrain_automapper_resolution_context_provider` → Backbrain\Automapper\Context\ResolutionContextProvider (aliased to `Backbrain\Automapper\Contract\ResolutionContextProviderInterface`).

## Registering Profiles

You can register profiles using attributes or manually via service tags.

### Using the AsProfile attribute (autoconfiguration)

```php
use Backbrain\Automapper\Contract\Attributes as Map;
use Backbrain\Automapper\Profile;
use Symfony\Component\Uid\Uuid;

#[Map\AsProfile]
final class GlobalProfile extends Profile
{
    public function __construct()
    {
        $this
            ->createMap(Uuid::class, 'string')
                ->convertUsing(fn (Uuid $source): string => $source->toRfc4122());
    }
}
```

If your `services.yaml` enables autoconfiguration (Symfony default), this attribute automatically adds the `backbrain_automapper_profile` tag so the profile is picked up by the compiler pass.

### Manual registration via service tag

```yaml
# config/services.yaml
services:
  App\Automapper\GlobalProfile:
    tags: ['backbrain_automapper_profile']
```

### Class scanning (paths)

If you configure `backbrain_automapper.paths`, the bundle will recursively scan those directories and register discovered classes with the AutoMapper factory. Ensure the classes are autoloadable (Composer PSR-4) and available at build time.

## Attributes

The bundle supports a set of PHP attributes that configure mappings declaratively. These attributes work without Symfony too, but in a Symfony app the container may be used to resolve classes passed as strings (service id or FQCN) and the ExpressionLanguage service is injected for evaluating expressions.

Notes:
- Property-level attributes that accept a `$source` class name (e.g., MapFrom, Condition) only apply when mapping from that specific source class to the destination type.
- If #[Ignore] is present on a property, that member is skipped regardless of other attributes.

### #[AsProfile] (class)
Already covered above. Marks a Profile class for autoconfiguration; the bundle tags it as `backbrain_automapper_profile`.

### #[MapTo] (class)
Declare a mapping from a source class to a destination class. Optional hooks allow conversion and before/after actions.

Constructor:
- dest: string (destination FQCN)
- convertUsing: TypeConverterInterface|class-string|null
- beforeMap: MappingActionInterface|class-string|null
- afterMap: MappingActionInterface|class-string|null

Example (using class names that are either instantiable or registered as services):
```php
use Backbrain\Automapper\Contract\Attributes as Map;
use App\Dto\ProfileDTO;
use App\Mapping\ProfileConverter;         // implements TypeConverterInterface
use App\Mapping\AuditBefore;              // implements MappingActionInterface
use App\Mapping\AuditAfter;               // implements MappingActionInterface

#[Map\MapTo(ProfileDTO::class, convertUsing: ProfileConverter::class, beforeMap: AuditBefore::class, afterMap: AuditAfter::class)]
final class AccountDTO
{
    public string $givenName;
    public string $familyName;
}
```
If you prefer Symfony services, register them and use their service IDs (strings) in place of class names; the bundle will fetch them from the container:
```yaml
# config/services.yaml
services:
  App\Mapping\ProfileConverter: ~
  App\Mapping\AuditBefore: ~
  App\Mapping\AuditAfter: ~
```

### #[NamingConvention] (class)
Set naming conventions for source or destination members. Apply on the source class to set the source member naming convention; apply on the destination class to set the destination member naming convention.

Constructor:
- convention: NamingConventionInterface

Example:
```php
use Backbrain\Automapper\Contract\Attributes as Map;
use Backbrain\Automapper\Converter\Naming\SnakeCaseNamingConvention;
use Backbrain\Automapper\Converter\Naming\PascalCaseNamingConvention;

#[Map\NamingConvention(new SnakeCaseNamingConvention())]
final class AccountDTO
{
    public string $given_name;
    public string $family_name;
}

#[Map\NamingConvention(new PascalCaseNamingConvention())]
final class ProfileDTO
{
    private string $GivenName;
    private string $FamilyName;
    // setters omitted
}
```

### #[ConstructUsing] (class)
Specify a factory used to create the destination object.

Constructor:
- constructUsing: TypeFactoryInterface

Example:
```php
use Backbrain\Automapper\Contract\Attributes as Map;
use Backbrain\Automapper\Contract\TypeFactoryInterface;

final class ProfileFactory implements TypeFactoryInterface
{
    public function __invoke(string $type): object { return new ProfileDTO(); }
}

#[Map\ConstructUsing(new ProfileFactory())]
final class ProfileDTO { /* ... */ }
```

### #[MapFrom] (property, repeatable)
Map a destination property from a specific source type using either a custom ValueResolver or a Symfony ExpressionLanguage expression.

Constructor:
- source: string (source FQCN this rule applies to)
- valueResolverOrExpression: ValueResolverInterface|Expression|string

Examples:
```php
use Backbrain\Automapper\Contract\Attributes as Map;
use Symfony\Component\ExpressionLanguage\Expression;

final class ProfileDTO
{
    #[Map\MapFrom(AccountDTO::class, 'source.givenName~" "~source.familyName')]
    private string $fullName;
}
```
Using a custom resolver:
```php
use Backbrain\Automapper\Contract\ValueResolverInterface;
use Backbrain\Automapper\Contract\ResolutionContextInterface;

final class FullNameResolver implements ValueResolverInterface
{
    public function resolve(object $source, ResolutionContextInterface $context): mixed
    {
        return $source->givenName.' '.$source->familyName;
    }
}

final class ProfileDTO
{
    #[Map\MapFrom(AccountDTO::class, new FullNameResolver())]
    private string $fullName;
}
```

### #[Condition] (property, repeatable)
Only map the member if the expression evaluates to true for the given source type.

Constructor:
- source: string (source FQCN)
- expression: Expression|string (Symfony EL; variables: source, context)

Example:
```php
use Backbrain\Automapper\Contract\Attributes as Map;

final class ProfileDTO
{
    #[Map\Condition(AccountDTO::class, 'source.isPublicProfile')]
    private ?string $email = null;
}
```

### #[NullSubstitute] (property)
Provide a substitute value when the source value is null.

Constructor:
- nullSubstitute: mixed

Example:
```php
use Backbrain\Automapper\Contract\Attributes as Map;

final class ProfileDTO
{
    #[Map\NullSubstitute('N/A')]
    private string $country;
}
```

### #[Ignore] (property)
Exclude a destination property from mapping entirely.

Example:
```php
use Backbrain\Automapper\Contract\Attributes as Map;

final class ProfileDTO
{
    #[Map\Ignore]
    private string $internalNote;
}
```

## Version Compatibility

- PHP: >= 8.2
- Symfony: 6.x and 7.x (per composer constraints `^6.0|^7.0` for framework-bundle/di/kernel)

This bundle is part of `backbrain/php-automapper`; no separate package is required.
