# Configuration

Configuration reference for customizing Traitify generators and trait behavior.

## Overview

Traitify works out of the box with sensible defaults, but provides extensive configuration options when you need them. This section covers the three-tier configuration system: package defaults, application-wide config, and per-model customization.

## Configuration Hierarchy

```
1. Model Property (Highest Priority)
   ↓
2. Configuration File
   ↓
3. Package Defaults (Lowest Priority)
```

## Quick Start

### Publish Configuration

```bash
php artisan vendor:publish --tag=traitify-config
```

This creates `config/traitify.php` in your application.

### App-Wide Configuration

Configure generators for all models in `config/traitify.php`:

```php
'generators' => [
    'token' => [
        'class' => TokenGenerator::class,
        'config' => [
            'length' => 64,
            'pool' => 'hex',
        ],
    ],
    'uuid' => [
        'class' => UuidGenerator::class,
        'config' => [
            'version' => 'v4',
        ],
    ],
],
```

### Per-Model Configuration

Override settings for specific models:

```php
class ApiKey extends Model
{
    use InteractsWithToken;

    protected $tokenGeneratorConfig = [
        'length' => 128,
        'prefix' => 'sk_',
    ];
}
```

## Generator Configuration

### TokenGenerator Options

- `length` (int): Token length (default: 128)
- `pool` (string): Character pool - auto|alpha|alphanumeric|numeric|hex
- `prefix` (string): Prefix to add
- `suffix` (string): Suffix to add
- `uppercase` (bool): Convert to uppercase

### UuidGenerator Options

- `version` (string): UUID version - ordered|v1|v3|v4|v5
- `format` (string): Output format - string|binary|hex
- `prefix` (string): Prefix to add
- `suffix` (string): Suffix to add
- `namespace` (string): For v3/v5
- `name` (string): For v3/v5

### SlugGenerator Options

- `separator` (string): Word separator (default: '-')
- `language` (string): Language for transliteration (default: 'en')
- `dictionary` (array): Custom character replacements
- `lowercase` (bool): Convert to lowercase
- `prefix` (string): Prefix to add
- `suffix` (string): Suffix to add
- `max_length` (int|null): Maximum slug length
- `unique` (bool): Ensure uniqueness

## Column Customization

Override default column names in your models:

```php
class Post extends Model
{
    use InteractsWithUuid;
    use InteractsWithSlug;

    protected $uuid_column = 'id';
    protected $slug_column = 'permalink';
    protected $slug_source_column = 'title';
}
```

## Environment-Specific Configuration

Adjust behavior based on environment:

```php
'generators' => [
    'token' => [
        'class' => TokenGenerator::class,
        'config' => [
            'length' => env('APP_ENV') === 'testing' ? 16 : 128,
        ],
    ],
],
```

## Related Documentation

- [Resolution Strategy](../02-architecture/03-resolution-strategy.md) - How configuration is resolved
- [Generators](../04-generators/README.md) - Generator reference
- [Examples](../06-examples/README.md) - Configuration examples
