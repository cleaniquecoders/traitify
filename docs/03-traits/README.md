# Traits

Comprehensive reference for all available Traitify traits.

## Overview

Traitify provides 12 reusable traits that add common functionality to your Laravel models and services. From automatic value generation (UUID, tokens, slugs) to data management (meta, tags, users) and utilities (search, enums, API responses), these traits eliminate boilerplate and standardize behavior across your application.

## Table of Contents

### [1. Overview](01-overview.md)

Summary of all 11 traits with quick examples, comparison table, and common usage patterns.

### Value Generation Traits

- **[2. InteractsWithTags](02-interacts-with-tags.md)** - Manage JSON tag arrays
- **[3. InteractsWithUuid](03-interacts-with-uuid.md)** - Auto-generate UUIDs
- **[4. InteractsWithToken](04-interacts-with-token.md)** - Generate secure tokens
- **[5. InteractsWithSlug](05-interacts-with-slug.md)** - Create URL-friendly slugs

### Data Management Traits

- **[6. InteractsWithMeta](06-interacts-with-meta.md)** - Manage JSON metadata
- **[7. InteractsWithUser](07-interacts-with-user.md)** - Auto-assign user IDs

### Utility Traits

- **[8. InteractsWithSearchable](08-interacts-with-searchable.md)** - Case-insensitive search
- **[9. InteractsWithDetails](09-interacts-with-details.md)** - Eager loading helpers
- **[10. InteractsWithApi](10-interacts-with-api.md)** - API response formatting
- **[11. InteractsWithEnum](11-interacts-with-enum.md)** - Enum helper methods
- **[12. InteractsWithResourceRoute](12-interacts-with-resource-route.md)** - Resource route URLs
- **[13. InteractsWithSqlViewMigration](13-interacts-with-sql-view-migration.md)** - SQL view migrations

### Logging Traits

- **[14. LogsOperations](14-logs-operations.md)** - Unified logging for operations

## Trait Categories

### Value Generation

- **InteractsWithUuid** - Auto-generate UUIDs
- **InteractsWithToken** - Generate secure tokens
- **InteractsWithSlug** - Create URL-friendly slugs

### Data Management

- **InteractsWithMeta** - Manage JSON metadata fields
- **InteractsWithTags** - Manage JSON tag arrays
- **InteractsWithUser** - Auto-assign authenticated user IDs
- **InteractsWithDetails** - Eager loading helpers

### API & Response

- **InteractsWithApi** - Standardized API response formatting
- **InteractsWithResourceRoute** - Generate resource route URLs

### Utilities

- **InteractsWithSearchable** - Case-insensitive search
- **InteractsWithEnum** - PHP enum helper methods
- **InteractsWithSqlViewMigration** - SQL view migration helpers

### Logging

- **LogsOperations** - Unified logging for Actions, Services, and Forms

## Related Documentation

- [Getting Started](../01-getting-started/README.md) - Basic usage examples
- [Generators](../04-generators/README.md) - Generator system for value traits
- [Examples](../06-examples/README.md) - Real-world usage patterns
