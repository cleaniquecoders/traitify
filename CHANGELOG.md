# Changelog

All notable changes to `Traitify` will be documented in this file.

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
