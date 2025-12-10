# InteractsWithUuid Trait

A Laravel trait that automatically generates UUIDs for your Eloquent models with support for multiple UUID versions and formats.

## Overview

The InteractsWithUuid trait provides automatic UUID generation on model creation, supports route model binding with UUIDs, and offers flexible configuration for different UUID versions and formats.

## Installation

This trait is part of the `cleaniquecoders/traitify` package.

```bash
composer require cleaniquecoders/traitify
```

## Database Setup

Add a UUID column to your table migration:

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->string('title');
    $table->timestamps();
});
```

### Using UUID as Primary Key

```php
Schema::create('posts', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('title');
    $table->timestamps();
});
```

## Basic Usage

### 1. Add the Trait to Your Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use CleaniqueCoders\Traitify\Concerns\InteractsWithUuid;

class Post extends Model
{
    use InteractsWithUuid;

    protected $fillable = ['title', 'content'];
}
```

### 2. UUIDs are Auto-Generated

```php
$post = Post::create(['title' => 'My Post']);
echo $post->uuid; // 9d9e8da7-78c3-4c9d-9f5e-5c8e4a2b1d3c
```

## Features

### Automatic UUID Generation

UUIDs are automatically generated when creating models:

```php
$post = new Post(['title' => 'Example']);
$post->save();
// UUID is automatically assigned before saving
```

### Query Scope

Find models by UUID using the query scope:

```php
$post = Post::uuid('9d9e8da7-78c3-4c9d-9f5e-5c8e4a2b1d3c')->first();
```

### Route Model Binding

The trait automatically sets up route model binding using the UUID:

```php
// In routes/web.php
Route::get('/posts/{post}', function (Post $post) {
    return view('posts.show', compact('post'));
});

// Access via: /posts/9d9e8da7-78c3-4c9d-9f5e-5c8e4a2b1d3c
```

### Custom Column Name

Override the default UUID column name:

```php
class Post extends Model
{
    use InteractsWithUuid;

    protected $uuid_column = 'external_id';
}
```

### UUID as Primary Key

Use UUID as your model's primary key:

```php
class Post extends Model
{
    use InteractsWithUuid;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $uuid_column = 'id';
}
```

## Configuration Options

### UUID Versions

Configure which UUID version to use:

```php
class Post extends Model
{
    use InteractsWithUuid;

    protected $uuidGeneratorConfig = [
        'version' => 'v4', // ordered, v1, v3, v4, v5
    ];
}
```

**Available Versions:**

- `ordered` (default) - Time-ordered for better database indexing
- `v1` - Time-based
- `v3` - Name-based (MD5)
- `v4` - Random
- `v5` - Name-based (SHA-1)

### Output Format

Configure the UUID output format:

```php
protected $uuidGeneratorConfig = [
    'format' => 'string', // string, binary, hex
];
```

**Available Formats:**

- `string` (default) - Standard UUID string format
- `binary` - Binary format for storage optimization
- `hex` - Hexadecimal format

### Prefix and Suffix

Add prefix or suffix to generated UUIDs:

```php
protected $uuidGeneratorConfig = [
    'prefix' => 'POST_',
    'suffix' => '_V1',
];
// Result: POST_9d9e8da7-78c3-4c9d-9f5e-5c8e4a2b1d3c_V1
```

### Name-Based UUIDs (v3/v5)

For version 3 or 5 UUIDs, provide namespace and name:

```php
protected $uuidGeneratorConfig = [
    'version' => 'v5',
    'namespace' => 'dns',
    'name' => 'example.com',
];
```

## App-Wide Configuration

Configure UUIDs globally in `config/traitify.php`:

```php
'generators' => [
    'uuid' => [
        'class' => \CleaniqueCoders\Traitify\Generators\UuidGenerator::class,
        'config' => [
            'version' => 'ordered',
            'format' => 'string',
        ],
    ],
],
```

## Advanced Examples

### External API Integration

```php
class Product extends Model
{
    use InteractsWithUuid;

    protected $uuidGeneratorConfig = [
        'version' => 'v4',
        'prefix' => 'PRD_',
    ];
}

$product = Product::create(['name' => 'Widget']);
// Share with external API: PRD_550e8400-e29b-41d4-a716-446655440000
```

### Multi-Tenant Application

```php
class Tenant extends Model
{
    use InteractsWithUuid;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $uuid_column = 'id';

    protected $uuidGeneratorConfig = [
        'version' => 'ordered', // Better for indexing
    ];
}
```

### Distributed Systems

```php
class Event extends Model
{
    use InteractsWithUuid;

    protected $uuidGeneratorConfig = [
        'version' => 'v1', // Time-based, includes node info
    ];
}
```

## Methods Reference

### Instance Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `getUuidColumnName()` | `string` | Get the UUID column name |
| `getRouteKeyName()` | `string` | Get route key name (overridden to use UUID) |

### Query Scopes

| Scope | Parameters | Description |
|-------|-----------|-------------|
| `uuid($value)` | `string` | Find model by UUID value |

## Best Practices

### 1. Use Ordered UUIDs for Primary Keys

```php
// Good for primary keys
protected $uuidGeneratorConfig = [
    'version' => 'ordered', // Better database performance
];

// Random UUIDs can cause index fragmentation
protected $uuidGeneratorConfig = [
    'version' => 'v4', // Use for non-primary key UUIDs
];
```

### 2. Index UUID Columns

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique()->index(); // Add index
    $table->timestamps();
});
```

### 3. Don't Overwrite UUIDs

```php
// UUIDs won't be overwritten if manually set
$post = Post::create(['uuid' => 'custom-uuid']);
// Will keep 'custom-uuid'
```

### 4. Combine with Other Traits

```php
class Post extends Model
{
    use InteractsWithUuid;
    use InteractsWithSlug;
    use InteractsWithUser;
}
```

## Testing

Example test cases:

```php
use Tests\TestCase;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InteractsWithUuidTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_generates_uuid_on_creation()
    {
        $post = Post::factory()->create();

        $this->assertNotNull($post->uuid);
        $this->assertIsString($post->uuid);
    }

    /** @test */
    public function it_can_find_by_uuid()
    {
        $post = Post::factory()->create();

        $found = Post::uuid($post->uuid)->first();

        $this->assertTrue($found->is($post));
    }

    /** @test */
    public function it_uses_uuid_for_route_model_binding()
    {
        $post = Post::factory()->create();

        $this->assertEquals('uuid', $post->getRouteKeyName());
    }
}
```

## Performance Considerations

### Indexing

Always index UUID columns for better query performance:

```sql
ALTER TABLE posts ADD INDEX idx_uuid (uuid);
```

### Storage Optimization

Use binary format for storage optimization:

```php
protected $uuidGeneratorConfig = [
    'format' => 'binary', // Saves ~50% storage vs string
];
```

But remember to convert when reading:

```php
protected $casts = [
    'uuid' => 'string',
];
```

## Troubleshooting

### UUID Not Generated

**Problem**: UUID is null after creating model.

**Solutions**:
1. Check column exists in database
2. Ensure column name matches (default: 'uuid')
3. Clear config cache: `php artisan config:clear`

### Route Model Binding Not Working

**Problem**: Routes not resolving by UUID.

**Solution**: Ensure `getRouteKeyName()` returns correct column:

```php
public function getRouteKeyName()
{
    return 'uuid'; // or $this->getUuidColumnName()
}
```

### UUID Already Exists Error

**Problem**: Duplicate UUID error (very rare).

**Solution**: This is extremely unlikely with v4 UUIDs. If it happens:
1. Use 'ordered' or 'v1' version instead
2. Check for manual UUID assignment conflicts

## Next Steps

- [InteractsWithToken](04-interacts-with-token.md) - Generate secure tokens
- [InteractsWithSlug](05-interacts-with-slug.md) - Create URL-friendly slugs
- [Generators](../04-generators/01-overview.md) - Learn about UUID generator options
