# Traitify

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cleaniquecoders/traitify.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/traitify)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/traitify/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/cleaniquecoders/traitify/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/traitify/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/cleaniquecoders/traitify/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/cleaniquecoders/traitify.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/traitify)

A Laravel package that streamlines development with reusable traits, contracts, and a powerful value generator system. Reduce boilerplate, standardize behavior, and enhance your models with automatic UUID, token, and slug generation.

## ✨ Features

- 🔧 **11 Reusable Traits** - UUID, Token, Slug, Meta, User, API, Search, and more
- 🎨 **Customizable Generators** - Flexible token, UUID, and slug generation
- ⚙️ **Three-Tier Configuration** - Model → Config → Default resolution
- 🔌 **Extensible Architecture** - Create custom generators easily
- 📦 **Zero Configuration** - Works out of the box with sensible defaults
- ✅ **100% Tested** - Comprehensive test coverage with Pest PHP

## 📦 Installation

```bash
composer require cleaniquecoders/traitify
```

## 🚀 Quick Start

```php
use CleaniqueCoders\Traitify\Concerns\InteractsWithUuid;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use InteractsWithUuid;

    // UUID automatically generated on creation
}
```

```php
$post = Post::create(['title' => 'Hello World']);
echo $post->uuid; // 9d9e8da7-78c3-4c9d-9f5e-5c8e4a2b1d3c
```

## 📚 Documentation

- **[Documentation Home](docs/README.md)** - Complete documentation index
- **[Getting Started](docs/01-getting-started/README.md)** - Installation and setup
- **[Architecture](docs/02-architecture/README.md)** - System design and patterns
- **[Traits Reference](docs/03-traits/README.md)** - All available traits
- **[Generators](docs/04-generators/README.md)** - Customizable value generation
- **[Configuration](docs/05-configuration/README.md)** - Configuration options
- **[Examples](docs/06-examples/README.md)** - Real-world usage examples
- **[Advanced](docs/07-advanced/README.md)** - Extend and customize

## 🔥 Popular Use Cases

### Auto-Generate UUIDs
```php
use InteractsWithUuid;

protected $uuid_column = 'id'; // Use UUID as primary key
```

### Secure API Tokens
```php
use InteractsWithToken;

protected $tokenGeneratorConfig = [
    'length' => 64,
    'prefix' => 'sk_',
    'pool' => 'hex',
];
```

### SEO-Friendly Slugs
```php
use InteractsWithSlug;

protected $slugGeneratorConfig = [
    'unique' => true,
    'max_length' => 100,
];
```

## 🧪 Testing

```bash
composer test
```

## 📖 Available Traits

| Trait | Purpose |
|-------|---------|
| `InteractsWithUuid` | Auto-generate UUIDs |
| `InteractsWithToken` | Generate secure tokens |
| `InteractsWithSlug` | Create URL-friendly slugs |
| `InteractsWithMeta` | Manage JSON metadata |
| `InteractsWithUser` | Auto-assign user relationships |
| `InteractsWithApi` | API response formatting |
| `InteractsWithSearchable` | Full-text search |
| `InteractsWithDetails` | Eager load relationships |
| `InteractsWithEnum` | Enum helper methods |
| `InteractsWithResourceRoute` | Resource route generation |
| `InteractsWithSqlViewMigration` | SQL view migrations |

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## 🔒 Security

If you discover any security issues, please review our [security policy](../../security/policy).

## 📝 Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.

## 👥 Credits

- [Nasrul Hazim Bin Mohamad](https://github.com/nasrulhazim)
- [All Contributors](../../contributors)

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
