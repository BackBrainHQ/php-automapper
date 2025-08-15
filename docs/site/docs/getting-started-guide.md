---
id: getting-started-guide
title: Getting Started Guide
sidebar_label: Getting Started Guide
sidebar_position: 1
---

import Tabs from '@theme/Tabs';
import TabItem from '@theme/TabItem';

# Getting Started Guide

## Installation

Use Composer to install PHP AutoMapper into your project:

```bash
composer require backbrain/php-automapper
```

## Usage


<Tabs defaultValue="example">
  <TabItem value="example" label="Example">
```php
<?php
// php docs/example/01_getting_started_usage.php

use Backbrain\Automapper\Contract\Builder\Options;
use Backbrain\Automapper\Contract\Builder\Config;
use Backbrain\Automapper\MapperConfiguration;

require_once __DIR__ . '/../../vendor/autoload.php';

$config = new MapperConfiguration(fn (Config $config) => $config
    ->createMap(AccountDTO::class, ProfileDTO::class)
    ->forMember(
        'fullName',
        fn (Options $opts) => $opts->mapFrom(
            fn (AccountDTO $source) => sprintf('%s %s', $source->givenName, $source->familyName)
        )
    )
);

$account = new AccountDTO();
$account->givenName = 'John';
$account->familyName = 'Doe';

$autoMapper = $config->createMapper();
$profile = $autoMapper->map($account, ProfileDTO::class);

dump($profile);

```
  </TabItem>
  <TabItem value="account_dto" label="AccountDTO.php">
```php
class AccountDTO {
    public string $givenName;
    public string $familyName;
}
```
  </TabItem>
  <TabItem value="profile_dto" label="ProfileDTO.php">
```php
class ProfileDTO {
    private string $givenName;
    private string $familyName;
    private string $fullName;

    public function setGivenName(string $givenName): ProfileDTO
    {
        $this->givenName = $givenName;
        return $this;
    }

    public function setFamilyName(string $familyName): ProfileDTO
    {
        $this->familyName = $familyName;
        return $this;
    }

    public function setFullName(string $fullName): ProfileDTO
    {
        $this->fullName = $fullName;
        return $this;
    }
}
```
  </TabItem>
</Tabs>

## Next steps

- Learn the full API, including mapIterable() for collections and mutate() for in-place updates: see the [AutoMapper API](./api-reference).
- Troubleshoot and understand exceptions: see [Error handling](./error-handling).
- Explore more topics under [Features](./category/features) and [Extensibility](./category/extensibility).