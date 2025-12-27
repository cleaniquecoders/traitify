# Traits Overview

Traitify provides 12 reusable traits for common Laravel application needs.

## Value Generation Traits

### InteractsWithToken

Automatically generates secure random tokens.

**[Full Documentation →](interacts-with-token.md)**

```php
use CleaniqueCoders\Traitify\Concerns\InteractsWithToken;

class ApiKey extends Model
{
    use InteractsWithToken;
}
```

### InteractsWithUuid

Generates UUIDs in multiple formats and versions.

**[Full Documentation →](interacts-with-uuid.md)**

```php
use CleaniqueCoders\Traitify\Concerns\InteractsWithUuid;

class Post extends Model
{
    use InteractsWithUuid;
}
```

### InteractsWithSlug

Creates SEO-friendly URL slugs from text.

**[Full Documentation →](interacts-with-slug.md)**

```php
use CleaniqueCoders\Traitify\Concerns\InteractsWithSlug;

class Article extends Model
{
    use InteractsWithSlug;
}
```

## Data Management Traits

### InteractsWithMeta

Manages JSON metadata fields with default values.

**[Full Documentation →](interacts-with-meta.md)**

```php
use CleaniqueCoders\Traitify\Concerns\InteractsWithMeta;

class Product extends Model
{
    use InteractsWithMeta;

    protected $default_meta = [
        'featured' => false,
        'priority' => 0,
    ];
}
```

### InteractsWithUser

Automatically assigns authenticated user IDs.

**[Full Documentation →](interacts-with-user.md)**

```php
use CleaniqueCoders\Traitify\Concerns\InteractsWithUser;

class Post extends Model
{
    use InteractsWithUser;
    // Auto-fills user_id from Auth::user()
}
```

### InteractsWithDetails

Provides eager loading helpers for related data.

**[Full Documentation →](interacts-with-details.md)**

```php
use CleaniqueCoders\Traitify\Concerns\InteractsWithDetails;

class Post extends Model
{
    use InteractsWithDetails;

    protected $with_details = ['author', 'comments'];
}
```

## API & Response Traits

### InteractsWithApi

Standardized API response formatting.

**[Full Documentation →](interacts-with-api.md)**

```php
use CleaniqueCoders\Traitify\Concerns\InteractsWithApi;
use CleaniqueCoders\Traitify\Contracts\Api;

class PostController implements Api
{
    use InteractsWithApi;

    public function index(Request $request)
    {
        return $this->getApiResponse($request);
    }
}
```

### InteractsWithResourceRoute

Generates resource route URLs.

**[Full Documentation →](interacts-with-resource-route.md)**

```php
use CleaniqueCoders\Traitify\Concerns\InteractsWithResourceRoute;

class Post extends Model
{
    use InteractsWithResourceRoute;
}

$post->getIndexRoute(); // posts.index
$post->getShowRoute();  // posts.show
```

## Utility Traits

### InteractsWithSearchable

Adds case-insensitive search capabilities.

**[Full Documentation →](interacts-with-searchable.md)**

```php
use CleaniqueCoders\Traitify\Concerns\InteractsWithSearchable;

class Post extends Model
{
    use InteractsWithSearchable;

    protected $searchable = ['title', 'content'];
}

Post::search('laravel')->get();
```

### InteractsWithEnum

Helper methods for PHP enums.

**[Full Documentation →](interacts-with-enum.md)**

```php
use CleaniqueCoders\Traitify\Concerns\InteractsWithEnum;

enum Status: string
{
    use InteractsWithEnum;

    case DRAFT = 'draft';
    case PUBLISHED = 'published';
}

Status::values();  // ['draft', 'published']
Status::labels();  // ['Draft', 'Published']
```

### InteractsWithSqlViewMigration

Helper for SQL view migrations.

**[Full Documentation →](interacts-with-sql-view-migration.md)**

```php
use CleaniqueCoders\Traitify\Concerns\InteractsWithSqlViewMigration;

class CreateUserStatsView extends Migration
{
    use InteractsWithSqlViewMigration;

    public function up()
    {
        $this->sqlUp('user_stats');
    }
}
```

## Logging Traits

### LogsOperations

Unified logging for Actions, Services, and Livewire Forms.

**[Full Documentation →](14-logs-operations.md)**

```php
use CleaniqueCoders\Traitify\Concerns\LogsOperations;
use CleaniqueCoders\Traitify\Contracts\HasLogging;

class OrderService implements HasLogging
{
    use LogsOperations;

    public function process(array $data)
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

## Trait Comparison

| Trait | Auto-Fill | Query Scope | Config | Custom Column |
|-------|-----------|-------------|--------|---------------|
| InteractsWithToken | ✅ | ✅ | ✅ | ✅ |
| InteractsWithUuid | ✅ | ✅ | ✅ | ✅ |
| InteractsWithSlug | ✅ | ✅ | ✅ | ✅ |
| InteractsWithMeta | ✅ | ❌ | ❌ | ✅ |
| InteractsWithUser | ✅ | ❌ | ❌ | ✅ |
| InteractsWithDetails | ❌ | ✅ | ❌ | ✅ |
| InteractsWithApi | ❌ | ❌ | ❌ | ❌ |
| InteractsWithResourceRoute | ❌ | ❌ | ✅ | ❌ |
| InteractsWithSearchable | ❌ | ✅ | ❌ | ✅ |
| InteractsWithEnum | ❌ | ❌ | ❌ | ❌ |
| InteractsWithSqlViewMigration | ❌ | ❌ | ❌ | ❌ |
| LogsOperations | ❌ | ❌ | ❌ | ❌ |

## Common Patterns

### Combining Traits

```php
class Post extends Model
{
    use InteractsWithUuid;
    use InteractsWithSlug;
    use InteractsWithUser;
    use InteractsWithMeta;

    protected $fillable = ['title', 'content'];
    protected $slug_source_column = 'title';
    protected $default_meta = ['views' => 0];
}
```

### Custom Columns

```php
class Product extends Model
{
    use InteractsWithToken;
    use InteractsWithMeta;

    protected $token_column = 'sku';
    protected $meta_column = 'attributes';
}
```

## Complete Trait Reference

### Value Generation Traits

- **[InteractsWithUuid](03-interacts-with-uuid.md)** - Auto-generate UUIDs with multiple versions and formats
- **[InteractsWithToken](04-interacts-with-token.md)** - Generate secure random tokens with configurable options
- **[InteractsWithSlug](05-interacts-with-slug.md)** - Create SEO-friendly URL slugs with transliteration

### Data Management Traits

- **[InteractsWithMeta](06-interacts-with-meta.md)** - Manage JSON metadata fields with default values
- **[InteractsWithTags](02-interacts-with-tags.md)** - Manage JSON tag arrays with query scopes
- **[InteractsWithUser](07-interacts-with-user.md)** - Auto-assign authenticated user IDs
- **[InteractsWithDetails](09-interacts-with-details.md)** - Eager loading helpers for relationships

### API & Response Traits

- **[InteractsWithApi](10-interacts-with-api.md)** - Standardized API response formatting
- **[InteractsWithResourceRoute](12-interacts-with-resource-route.md)** - Generate resource route URLs automatically

### Utility Traits

- **[InteractsWithSearchable](08-interacts-with-searchable.md)** - Case-insensitive search across fields
- **[InteractsWithEnum](11-interacts-with-enum.md)** - PHP enum helper methods for values and labels
- **[InteractsWithSqlViewMigration](13-interacts-with-sql-view-migration.md)** - SQL view migration helpers

### Logging Traits

- **[LogsOperations](14-logs-operations.md)** - Unified logging for Actions, Services, and Forms

## Next Steps

- [Getting Started](../01-getting-started/README.md) - Basic trait usage examples
- [Generators](../04-generators/README.md) - Learn about the generator system
- [Examples](../06-examples/README.md) - Real-world usage patterns
