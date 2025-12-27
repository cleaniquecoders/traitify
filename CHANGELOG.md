# Changelog

All notable changes to `Traitify` will be documented in this file.

## 1.3.1 - 2025-12-27

### 1.3.1 - Unified Logging Trait - 2025-12-27

#### New Features

##### LogsOperations Trait

Unified logging trait for consistent operation logging across Actions, Services, and Livewire Forms.

**New Contract:**

- `HasLogging` - Interface defining core logging methods

**New Trait:**

- `LogsOperations` - Combines and standardizes logging functionality

###### Core Logging Methods

- `logDebug()`, `logInfo()`, `logWarning()`, `logError()` - Basic log levels
- `logException()` - Exception logging with full stack trace

###### Operation Lifecycle

- `logOperationStart()` - Mark operation beginning
- `logOperationSuccess()` - Mark successful completion
- `logOperationFailure()` - Mark operation failure

###### Method Lifecycle

- `logMethodEntry()` - Log method entry with sanitized parameters
- `logMethodSuccess()` - Log method success with summarized result
- `logMethodFailure()` - Log method failure

###### Form-Specific Logging

- `logValidation()` - Log validation passed/failed
- `logStateChange()` - Log field state changes

###### Service-Specific Logging

- `logApiCall()` - Log outgoing API requests
- `logApiResponse()` - Log API responses (debug for success, warning for errors)
- `logCacheOperation()` - Log cache hits/misses

###### Context Helpers

- `dbContext()` - Create database operation context
- `externalServiceContext()` - Create external service context

###### Automatic Features

- **Context Enrichment** - Automatically includes class name, user_id, uuid, and state keys
- **Sensitive Data Sanitization** - Redacts passwords, tokens, API keys, credentials
- **Data Summarization** - Summarizes large arrays/objects to prevent log bloat

##### Usage Example

 ```php
 use CleaniqueCoders\Traitify\Concerns\LogsOperations;
use CleaniqueCoders\Traitify\Contracts\HasLogging;

class OrderService implements HasLogging
{
    use LogsOperations;

    public function processOrder(array $data)
    {
        $this->logOperationStart('Order processing');

        try {
            // Process order...
            $this->logOperationSuccess('Order processing');
        } catch (Throwable $e) {
            $this->logException($e, 'Order processing');
            throw $e;
        }
    }
}

 ```
##### Documentation

- Added docs/03-traits/14-logs-operations.md - Full documentation for the trait
- Updated docs/03-traits/README.md - Added LogsOperations entry
- Updated docs/03-traits/01-overview.md - Added LogsOperations section

##### Tests

- Added 33 new unit tests covering all logging functionality
- Tests include: basic logging, exception handling, lifecycle logging, validation, API logging, cache logging, context
  enrichment, sensitive data sanitization, and data summarization

## Customizable Value Generator System & Updating Documentation - 2025-12-10

#### Documentation

All documentations are updated and moved to [`docs/`](https://github.com/cleaniquecoders/traitify/tree/main/docs) directory.

#### Customizable Value Generator System

Introduced a flexible, extensible generator system for tokens, UUIDs, and slugs with three-tier configuration support.

##### New Components

**Contracts & Interfaces:**

- `ValueGenerator` interface - Defines standard contract for all generators
- Supports `generate()`, `validate()`, `getConfig()`, and `setConfig()` methods

**Generator Classes:**

- **TokenGenerator** - Configurable token generation
  
  - Multiple character pools: `auto`, `alpha`, `alphanumeric`, `numeric`, `hex`
  - Configurable length (default: 128)
  - Prefix/suffix support
  - Uppercase option
  
- **UuidGenerator** - Multiple UUID version support
  
  - Versions: `ordered` (default), `v1`, `v3`, `v4`, `v5`
  - Output formats: `string`, `binary`, `hex`
  - Prefix/suffix support
  - Custom namespace/name for v3/v5
  
- **SlugGenerator** - Advanced slug generation
  
  - Custom separators
  - Language support
  - Dictionary mappings
  - Max length constraints
  - Uniqueness checking
  - Case preservation option
  

**Configuration System:**

- New `config/traitify.php` configuration file
- Three-tier resolution: Model Property → Config File → Default
- Per-model generator customization via properties
- App-wide defaults via config file

**Architecture:**

- `AbstractValueGenerator` - Base class with shared functionality
- `HasGeneratorResolver` trait - Generator resolution logic
- Dot notation config access support

### 🔄 Enhancements

#### Refactored Traits (Backward Compatible)

- `InteractsWithToken` - Now uses configurable `TokenGenerator`
- `InteractsWithUuid` - Now uses configurable `UuidGenerator`
- `InteractsWithSlug` - Now uses configurable `SlugGenerator`

#### Service Provider

- Added config file publishing support via `hasConfigFile()`
- Use `php artisan vendor:publish --tag=traitify-config` to publish

### 📚 Usage Examples

#### App-wide Configuration

  ```php
  // config/traitify.php
'generators' => [
  'token' => [
      'class' => \CleaniqueCoders\Traitify\Generators\TokenGenerator::class,
      'config' => [
          'length' => 64,
          'prefix' => 'API_',
          'uppercase' => true,
      ],
  ],
  'uuid' => [
      'class' => \CleaniqueCoders\Traitify\Generators\UuidGenerator::class,
      'config' => [
          'version' => 'v4',
          'format' => 'string',
      ],
  ],
  'slug' => [
      'class' => \CleaniqueCoders\Traitify\Generators\SlugGenerator::class,
      'config' => [
          'separator' => '_',
          'max_length' => 100,
          'unique' => true,
      ],
  ],
],


  ```
Per-Model Customization

```php
  use CleaniqueCoders\Traitify\Concerns\InteractsWithToken;
  use Illuminate\Database\Eloquent\Model;

  class ApiKey extends Model
  {
      use InteractsWithToken;

      // Option 1: Use a custom generator class
      protected $tokenGenerator = \App\Generators\MyCustomTokenGenerator::class;

      // Option 2: Configure the default generator for this model
      protected $tokenGeneratorConfig = [
          'length' => 32,
          'pool' => 'hex',
          'prefix' => 'sk_',
      ];
  }


```
Custom Generator Implementation

```php
  use CleaniqueCoders\Traitify\Generators\AbstractValueGenerator;

  class MyCustomTokenGenerator extends AbstractValueGenerator
  {
      protected function getDefaultConfig(): array
      {
          return [
              'format' => 'custom',
              'length' => 40,
          ];
      }

      public function generate(array $context = []): mixed
      {
          // Your custom generation logic
          $length = $this->getConfigValue('length', 40);
          return bin2hex(random_bytes($length / 2));
      }

      public function validate(mixed $value, array $context = []): bool
      {
          // Your validation logic
          return is_string($value) && strlen($value) === $this->getConfigValue('length', 40);
      }
  }


```
🔒 Backward Compatibility

100% backward compatible - No breaking changes:

- ✅ Existing models work without any changes
- ✅ Default behavior unchanged (Token: 128 chars, UUID: ordered, Slug: from name)
- ✅ All column customization properties still work ($token_column, $uuid_column, etc.)
- ✅ No migration required
- ✅ Opt-in enhancement - use new features when you need them

🚀 Upgrade Guide

No upgrade steps required! The changes are fully backward compatible.

Optional: Publish the config file to customize generators app-wide:

```bash
  php artisan vendor:publish --tag=traitify-config


```
This will create `config/traitify.php` in your Laravel application.

## Added Interact with Tag - 2025-11-01

See [here](https://github.com/cleaniquecoders/traitify/blob/main/docs/interacts-with-tags.md) for more details.

## Added Interaction with Slug - 2025-10-10

**Full Changelog**: https://github.com/cleaniquecoders/traitify/compare/1.1.0...1.2.0

## 1.1.0 - 2025-05-01

**Full Changelog**: https://github.com/cleaniquecoders/traitify/compare/v1.0.2...1.1.0

## v1.0.2 - 2024-11-27

- Added #1

**Full Changelog**: https://github.com/cleaniquecoders/traitify/compare/v1.0.1...v1.0.2

## v1.0.1 - 2024-10-16

### Traitify v1.0.1 Release Notes

**Release Date**: 15th October 2024

This patch release focuses on improvements to the **`InteractsWithSqlViewMigration`** trait, which simplifies SQL view management in Laravel migrations.

#### Key Updates in v1.0.1:

1. **InteractsWithSqlViewMigration Trait**:
   - Automates the process of managing SQL views during migrations.
   - Supports the use of external SQL files to create and drop views.
   - Ensures better error handling with exceptions when SQL files are missing.
   - Customizable filenames for both creating and dropping views via `getUpFilename()` and `getDownFilename()` methods.
   

#### Installation:

To update to v1.0.1, run:

```bash
composer update cleaniquecoders/traitify







```
#### Documentation:

For more details on how to use the `InteractsWithSqlViewMigration` trait, please refer to the [GitHub repository](https://github.com/cleaniquecoders/traitify).


---

This update enhances how SQL views are handled in migrations, making it easier to maintain and organize SQL scripts in your Laravel projects.

**Full Changelog**: https://github.com/cleaniquecoders/traitify/compare/v1.0.0...v1.0.1

## v1.0.0 - 2024-10-15

### Release Notes for Traitify v1.0.0

**Release Date**: 15th October 2024

We are excited to announce the first official release of **Traitify** (v1.0.0), a Laravel package that provides a set of reusable traits and contracts to streamline application development and enforce best practices.

#### New Features:

1. **InteractsWithUuid Trait**:
   
   - Automatically generates UUIDs for models during creation.
   - Supports custom UUID column names.
   - Provides query scope for filtering models by UUID.
   
2. **InteractsWithUser Trait**:
   
   - Automatically assigns the authenticated user ID to models during creation.
   - Supports custom user ID column names.
   - Works seamlessly with Laravel's `Auth` facade.
   
3. **InteractsWithToken Trait**:
   
   - Automatically generates random 128-character tokens for models.
   - Supports custom token column names.
   - Provides query scope for filtering models by token.
   
4. **InteractsWithSearchable Trait**:
   
   - Adds case-insensitive search functionality to models.
   - Supports searching across single or multiple fields.
   
5. **InteractsWithResourceRoute Trait**:
   
   - Helps in generating resource URLs (e.g., `index`, `show`) for models.
   - Automatically derives route base names from model names.
   
6. **InteractsWithMeta Trait**:
   
   - Manages meta fields dynamically in models.
   - Automatically adds casts for the `meta` attribute as an array.
   - Supports default meta values.
   
7. **InteractsWithEnum Trait**:
   
   - Provides methods to handle enum values, labels, and options with descriptions.
   - Supports usage in select inputs for better UX.
   
8. **InteractsWithDetails Trait**:
   
   - Allows models to define related details and apply eager loading.
   - Provides a query scope to load related details efficiently.
   
9. **InteractsWithApi Trait**:
   
   - Simplifies API response structure with methods for data, messages, and status codes.
   - Supports customization of API responses.
   

#### Contracts:

1. **Builder Contract**:
   
   - Defines a `build()` method that returns the instance of the class implementing it.
   
2. **Execute Contract**:
   
   - Defines an `execute()` method that returns the instance of the class implementing it.
   
3. **Menu Contract**:
   
   - Defines a `menus()` method that returns a collection of menu items.
   
4. **Processor Contract**:
   
   - Defines a `process()` method that returns the instance of the class implementing it.
   

#### Improvements & Enhancements:

- Enhanced unit tests for each trait and contract, ensuring reliable functionality.
- Compatibility with Laravel's core features like UUIDs, meta fields, token management, and API responses.
- Designed to be modular, flexible, and easy to extend.

#### Installation:

You can install the package via Composer:

```bash
composer require cleaniquecoders/traitify








```
#### Documentation:

Full documentation and examples are available in the repository’s README.

**Full Changelog**: https://github.com/cleaniquecoders/traitify/commits/v1.0.0
