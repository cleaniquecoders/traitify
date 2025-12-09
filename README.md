[![Latest Version on Packagist](https://img.shields.io/packagist/v/cleaniquecoders/traitify.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/traitify) [![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/traitify/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/cleaniquecoders/traitify/actions?query=workflow%3Arun-tests+branch%3Amain) [![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/traitify/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/cleaniquecoders/traitify/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain) [![Total Downloads](https://img.shields.io/packagist/dt/cleaniquecoders/traitify.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/traitify)

# Traitify

**Traitify** is a Laravel package designed to streamline and enhance your development process by providing a collection of reusable traits and contracts. It allows developers to easily integrate common functionalities into their Laravel applications while adhering to a consistent, maintainable codebase.

With **Traitify**, you can reduce boilerplate code, simplify repetitive tasks, and standardize behavior across your application. It offers a clean, modular approach to sharing common functionality through traits, while contracts provide flexibility and extensibility for more complex behavior patterns.

## Key Features

- **Reusable Traits**: Simplify and standardize common functionalities such as logging, validation, and caching.
- **Extensible Contracts**: Ensure your application is easily extendable with well-defined contracts.
- **Modular and Customizable**: Pick and choose the traits and contracts you need for your specific project.

## Installation

You can install the package via Composer:

```bash
composer require cleaniquecoders/traitify
```

## Usage

This package provides a collection of reusable traits and contracts that can be easily integrated into your Laravel applications. You can use the traits to add common functionality to your models, controllers, or other classes, and the contracts to ensure consistent interfaces for your classes.

### Traits

Below are the available traits under the `src/Concerns` directory:

- **InteractsWithApi**: Provides methods to interact with APIs.
- **InteractsWithDetails**: Handles interactions with detailed data.
- **InteractsWithEnum**: Facilitates the use of enums in your models or classes.
- **InteractsWithMeta**: Provides functionality for managing meta fields.
- **InteractsWithResourceRoute**: Adds support for handling resource routes.
- **InteractsWithSearchable**: Adds searching capabilities to your models or queries.
- **InteractsWithSlug**: Automatically generates and manages slugs for your models.
- **InteractsWithToken**: Handles operations related to tokens.
- **InteractsWithUser**: Provides methods to interact with users.
- **InteractsWithUuid**: Adds UUID support to your models or other classes.
- **InteractsWithTags**: Allow interactions with tag field (JSON Data Type)

### Contracts

Here are the available interfaces under the `src/Contracts` directory:

- **Api**: Defines the structure for interacting with APIs.
- **Builder**: Provides a contract for builder classes.
- **Enum**: Ensures proper implementation of enums.
- **Execute**: Defines an execution contract for action-based classes.
- **Menu**: Provides a contract for building and managing menus.
- **Processor**: Ensures the implementation of data processing workflows.

### Example Usage

You can easily incorporate these traits and contracts into your Laravel application like this:

```php
use CleaniqueCoders\Traitify\Concerns\InteractsWithApi;
use CleaniqueCoders\Traitify\Contracts\Api;

class ExampleClass implements Api
{
    use InteractsWithApi;

    // Class logic here
}
```

For a full list of traits and contracts and detailed usage examples, explore the [Contracts](src/Contracts) and [Concerns](src/Concerns) directories.

## Testing

To run the tests, use:

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

We welcome contributions! Please see [CONTRIBUTING](CONTRIBUTING.md) for details on how to get involved.

## Security Vulnerabilities

If you discover any security issues, please review our [security policy](../../security/policy) for reporting vulnerabilities.

## Credits

- [Nasrul Hazim Bin Mohamad](https://github.com/nasrulhazim)
- [All Contributors](../../contributors)

## License

This package is open-sourced software licensed under the [MIT License](LICENSE.md).
