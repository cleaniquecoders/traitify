# Installation

## Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher
- Composer

## Install via Composer

```bash
composer require cleaniquecoders/traitify
```

The package will automatically register its service provider.

## Publish Configuration (Optional)

If you want to customize the default generators, publish the configuration file:

```bash
php artisan vendor:publish --tag=traitify-config
```

This will create `config/traitify.php` in your Laravel application.

## Verify Installation

Run the package tests to ensure everything is working:

```bash
vendor/bin/pest vendor/cleaniquecoders/traitify
```

## Next Steps

- [Quick Start Guide](02-quick-start.md)
- [Basic Usage](03-basic-usage.md)
- [Configuration](../05-configuration/README.md)
