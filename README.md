# PHP AutoMapper

PHP AutoMapper is a library designed to simplify the mapping of data between objects, inspired by the 
popular .NET library AutoMapper. It aims to reduce boilerplate code necessary for transferring data from 
one object structure to another, making your PHP application cleaner and maintenance easier.

**Note:** This project is still in alpha development and may not yet support all features of the original AutoMapper library.
Interfaces and methods may change even in minor releases until a stable version v1.x is reached.

## Installation

Use Composer to install PHP AutoMapper into your project:

```bash
composer require backbrain/php-automapper
```

## Features

PHP AutoMapper strives to implement the core functionalities of the original .NET AutoMapper library. 
Here's a list of supported features:

- [x] Convention-based mapping
- [x] Custom value resolvers
- [x] Nested object mapping
- [x] Conditional property mapping
- [x] Custom value converters
- [x] Before and after mapping actions
- [x] Support for AutoMapper profiles
- [ ] Reverse mapping
- [ ] Inline mapping configuration
- [x] Mapping to existing objects

Please note that due to differences between C# and PHP, not all features from the original AutoMapper 
library are applicable or have been implemented at this stage.

## Documentation

For a detailed documentation, please refer to the [PHP AutoMapper Documentation](https://backbrainhq.github.io/php-automapper) site.

For general usage patterns and understanding AutoMapper concepts, please refer to the original AutoMapper
documentation:

[.NET AutoMapper Documentation](https://docs.automapper.org/en/latest/)

The concepts and configurations explained in the original documentation serve as a basis for understanding
how to use PHP AutoMapper effectively. Where PHP AutoMapper diverges or extends the original library's functionality, specific documentation and examples will be provided within this project's wiki or documentation directory.


## Usage Example 

Here's a simple example of how to use PHP AutoMapper to map data between two objects.
For more examples and detailed usage instructions, please refer to the [examples](docs/example) directory.

```php
<?php
// php docs/example/01_basic.php
use Backbrain\Automapper\Contract\Builder\Options;
use Backbrain\Automapper\Contract\Builder\Config;
use Backbrain\Automapper\MapperConfiguration;

require_once __DIR__ . '/../../vendor/autoload.php';

class AccountDTO {
    public string $givenName;
    public string $familyName;
}

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
The dump shows the new ProfileDTO with the mapped properties:
```
^ ProfileDTO^ {#45
  -givenName: "John"
  -familyName: "Doe"
  -fullName: "John Doe"
}
```

For more examples and detailed usage instructions, please refer to the [examples](docs/example) directory.

## Contributing

Contributions to PHP AutoMapper are welcome! Whether it's adding new features, improving existing ones, 
or writing documentation, your help is appreciated. Please refer to the CONTRIBUTING.md file for guidelines on how to contribute to this project.

### Commit messages

We are using [Angular Commit Message Conventions](https://github.com/angular/angular.js/blob/master/DEVELOPERS.md#-git-commit-guidelines).

```
<type>(<scope>): <subject>
<BLANK LINE>
<body>
<BLANK LINE>
<footer>
```

The `<type>` must be one of the following:

- `feat`: A new feature
- `fix`: A bug fix
- `docs`: Documentation only changes
- `style`: Changes that do not affect the meaning of the code (white-space, formatting, missing semi-colons, etc)
- `refactor`: A code change that neither fixes a bug nor adds a feature
- `perf`: A code change that improves performance
- `test`: Adding missing or correcting existing tests
- `chore`: Changes to the build process or auxiliary tools and libraries such as documentation generation

## License

PHP AutoMapper is open-sourced software licensed under the [MIT license](LICENSE).

---

This project is not affiliated with the original AutoMapper project but is inspired by its functionality 
and aims to bring similar capabilities to the PHP community.
