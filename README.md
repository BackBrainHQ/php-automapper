# PHP AutoMapper

PHP AutoMapper is a library designed to simplify the mapping of data between objects, inspired by the popular .NET library AutoMapper. It aims to reduce boilerplate code necessary for transferring data from one object structure to another, making your PHP application cleaner and maintenance easier.

## Installation

Use Composer to install PHP AutoMapper into your project:

```bash
composer require backbrain/php-automapper
```

## Features

PHP AutoMapper strives to implement the core functionalities of the original .NET AutoMapper library. Here's a list of supported features:

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

Please note that due to differences between C# and PHP, not all features from the original AutoMapper library are applicable or have been implemented at this stage.

## Documentation

For general usage patterns and understanding AutoMapper concepts, please refer to the original AutoMapper documentation:

[AutoMapper Documentation](https://docs.automapper.org/en/latest/)

The concepts and configurations explained in the original documentation serve as a basis for understanding how to use PHP AutoMapper effectively. Where PHP AutoMapper diverges or extends the original library's functionality, specific documentation and examples will be provided within this project's wiki or documentation directory.

## Contributing

Contributions to PHP AutoMapper are welcome! Whether it's adding new features, improving existing ones, or writing documentation, your help is appreciated. Please refer to the CONTRIBUTING.md file for guidelines on how to contribute to this project.

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

This project is not affiliated with the original AutoMapper project but is inspired by its functionality and aims to bring similar capabilities to the PHP community.
