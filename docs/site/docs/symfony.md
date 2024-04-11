---
id: symfony
title: Symfony
sidebar_label: Symfony
sidebar_position: 6
---

# Symfony

PHP AutoMapper can be used with Symfony to map objects between different classes. This guide will show you how to set up PHP AutoMapper in a Symfony project.

## Installation

As soon as you installed AutoMapper via Composer, you can start using it in your Symfony project.
Thanks to Symfony Flex, AutoMapper will be automatically registered as a bundle in your Symfony application.

In your `config/bundles.php` file, you should see the following line:

```php
return [
    // ...
    Backbrain\Automapper\BackbrainAutomapperBundle::class => ['all' => true],
];
``` 

## Configuration

To configure AutoMapper, you might create a new service yaml file under `config/packages/backbrain_automapper.yaml`.

This is optional since AutoMapper will work out of the box with default settings:
```yaml
# default configuration
backbrain_automapper:
  cache_adapter: cache.app
  logger: logger
```

## Usage

### Dependency Injection

You can inject the `AutoMapper` service into your controllers or services by using auto-wire the `AutoMapperInterface` interface.

```php
class ApiService
{
    public function __construct(
        private AutoMapperInterface $autoMapper,
    ) {}
}
```

Alternatively, you can do it manually using the `backbrain_automapper` service id:
```yaml
services:
    App\Service\ApiService:
        arguments:
            $autoMapper: '@backbrain_automapper'
```

### Attributes

For convenience, AutoMapper provides attributes to configure you AutoMapper instance.

#### AsProfile

```php
use Backbrain\Automapper\AsProfile;
use Backbrain\Automapper\Profile;
use Symfony\Component\Uid\Uuid;

#[AsProfile]
class GlobalProfile extends Profile
{
    public function __construct()
    {
        $this
            ->createMap(Uuid::class, 'string')
                ->convertUsing(fn (Uuid $source): string => $source->toRfc4122())
        ;
    }
}
```

This automatically registers the profile with the AutoMapper instance by tagging the service
as `backbrain_automapper_profile`.
