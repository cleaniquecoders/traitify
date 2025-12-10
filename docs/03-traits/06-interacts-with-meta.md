# InteractsWithMeta Trait

A Laravel trait that provides automatic default metadata management for your Eloquent models using JSON columns.

## Overview

The InteractsWithMeta trait automatically populates a meta JSON column with default values when creating models, perfect for storing flexible model attributes and settings.

## Installation

This trait is part of the `cleaniquecoders/traitify` package.

```bash
composer require cleaniquecoders/traitify
```

## Database Setup

Add a JSON meta column to your table migration:

```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->json('meta')->nullable();
    $table->timestamps();
});
```

## Basic Usage

### 1. Add the Trait to Your Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use CleaniqueCoders\Traitify\Concerns\InteractsWithMeta;

class Product extends Model
{
    use InteractsWithMeta;

    protected $fillable = ['name'];

    protected $casts = [
        'meta' => 'array',
    ];

    protected $default_meta = [
        'featured' => false,
        'priority' => 0,
        'tags' => [],
    ];
}
```

### 2. Default Meta is Auto-Applied

```php
$product = Product::create(['name' => 'Widget']);

echo json_encode($product->meta);
// {"featured":false,"priority":0,"tags":[]}
```

## Features

### Automatic Default Values

When creating a model, the `meta` column is automatically populated with values from `$default_meta`:

```php
$product = new Product(['name' => 'Gadget']);
$product->save();

// Meta is automatically set to default values
print_r($product->meta);
// Array
// (
//     [featured] => false
//     [priority] => 0
//     [tags] => Array()
// )
```

### Preserves Manual Values

If you manually set meta values, they won't be overwritten:

```php
$product = Product::create([
    'name' => 'Special Product',
    'meta' => [
        'featured' => true,
        'priority' => 10,
        'custom_field' => 'value',
    ],
]);

// Manual meta is preserved
```

### Merge with Defaults

You can merge custom values with defaults:

```php
$product = new Product(['name' => 'Product']);
$product->meta = array_merge(
    $product->defaultMeta(),
    ['featured' => true]
);
$product->save();

// Result: {featured: true, priority: 0, tags: []}
```

## Configuration

### Define Default Meta Values

Use the `$default_meta` property to define default metadata:

```php
class Product extends Model
{
    use InteractsWithMeta;

    protected $default_meta = [
        'featured' => false,
        'priority' => 0,
        'status' => 'draft',
        'settings' => [
            'visible' => true,
            'searchable' => true,
        ],
    ];
}
```

### Custom Meta Column Name

Override the default meta column name:

```php
class Product extends Model
{
    use InteractsWithMeta;

    // Trait automatically looks for 'meta' column
    // But you can use a different column by updating your logic
}
```

## Common Use Cases

### Product Settings

```php
class Product extends Model
{
    use InteractsWithMeta;

    protected $default_meta = [
        'featured' => false,
        'on_sale' => false,
        'discount_percentage' => 0,
        'stock_status' => 'in_stock',
        'shipping' => [
            'free_shipping' => false,
            'weight' => 0,
            'dimensions' => [],
        ],
    ];
}

$product = Product::create(['name' => 'Laptop']);
$product->meta['on_sale'] = true;
$product->meta['discount_percentage'] = 15;
$product->save();
```

### User Preferences

```php
class User extends Model
{
    use InteractsWithMeta;

    protected $default_meta = [
        'theme' => 'light',
        'language' => 'en',
        'notifications' => [
            'email' => true,
            'push' => false,
            'sms' => false,
        ],
        'privacy' => [
            'profile_public' => true,
            'show_email' => false,
        ],
    ];
}

$user = User::create(['name' => 'John', 'email' => 'john@example.com']);
$user->meta['theme'] = 'dark';
$user->save();
```

### Post Metadata

```php
class Post extends Model
{
    use InteractsWithMeta;

    protected $default_meta = [
        'views' => 0,
        'likes' => 0,
        'seo' => [
            'title' => '',
            'description' => '',
            'keywords' => [],
        ],
        'publishing' => [
            'featured' => false,
            'allow_comments' => true,
            'published_at' => null,
        ],
    ];
}

$post = Post::create(['title' => 'My Post']);
$post->increment Likes();
```

### Configuration Settings

```php
class Setting extends Model
{
    use InteractsWithMeta;

    protected $default_meta = [
        'site' => [
            'name' => 'My Site',
            'tagline' => '',
            'logo' => null,
        ],
        'email' => [
            'from_name' => 'System',
            'from_address' => 'noreply@example.com',
        ],
        'features' => [
            'registration_enabled' => true,
            'api_enabled' => false,
        ],
    ];
}
```

## Advanced Examples

### Dynamic Default Meta

```php
class Product extends Model
{
    use InteractsWithMeta;

    public function defaultMeta(): array
    {
        return [
            'created_by' => auth()->id(),
            'created_at_timestamp' => now()->timestamp,
            'version' => config('app.version'),
            'environment' => app()->environment(),
        ];
    }
}
```

### Accessing Meta Values

```php
$product = Product::find(1);

// As array
$featured = $product->meta['featured'];

// Using data_get for nested values
$freeShipping = data_get($product->meta, 'shipping.free_shipping');

// With default value
$status = data_get($product->meta, 'status', 'active');
```

### Updating Meta Values

```php
// Update specific key
$product->meta = array_merge($product->meta, [
    'featured' => true,
]);
$product->save();

// Or use JSON column update (Laravel 5.3+)
$product->update([
    'meta->featured' => true,
]);
```

### Querying Meta Values

```php
// Find products that are featured
$featured = Product::where('meta->featured', true)->get();

// Find products with priority > 5
$priority = Product::where('meta->priority', '>', 5)->get();

// Find products with free shipping
$freeShipping = Product::where('meta->shipping->free_shipping', true)->get();
```

## Working with Accessors

Create accessors for common meta values:

```php
class Product extends Model
{
    use InteractsWithMeta;

    protected $default_meta = [
        'featured' => false,
        'priority' => 0,
    ];

    public function getIsFeaturedAttribute(): bool
    {
        return $this->meta['featured'] ?? false;
    }

    public function setIsFeaturedAttribute(bool $value): void
    {
        $meta = $this->meta ?? [];
        $meta['featured'] = $value;
        $this->meta = $meta;
    }
}

// Usage
$product->is_featured = true;
$product->save();
```

## Methods Reference

### Instance Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `defaultMeta()` | `array` | Returns default meta values |

## Best Practices

### 1. Always Cast Meta as Array

```php
protected $casts = [
    'meta' => 'array',
];
```

### 2. Define Comprehensive Defaults

```php
// Good - complete structure
protected $default_meta = [
    'status' => 'draft',
    'featured' => false,
    'settings' => [
        'visible' => true,
    ],
];

// Avoid - incomplete structure
protected $default_meta = [];
```

### 3. Use Data Get for Nested Values

```php
// Safe - won't error if key doesn't exist
$value = data_get($product->meta, 'shipping.free_shipping', false);

// Risky - can cause errors
$value = $product->meta['shipping']['free_shipping'];
```

### 4. Document Meta Structure

```php
/**
 * @property array $meta
 * Meta structure:
 * - featured (bool): Whether product is featured
 * - priority (int): Display priority (0-100)
 * - tags (array): Product tags
 */
class Product extends Model
{
    use InteractsWithMeta;
}
```

## Testing

Example test cases:

```php
use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InteractsWithMetaTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_applies_default_meta_on_creation()
    {
        $product = Product::factory()->create();

        $this->assertIsArray($product->meta);
        $this->assertFalse($product->meta['featured']);
        $this->assertEquals(0, $product->meta['priority']);
    }

    /** @test */
    public function it_preserves_manual_meta_values()
    {
        $product = Product::factory()->create([
            'meta' => ['featured' => true, 'custom' => 'value'],
        ]);

        $this->assertTrue($product->meta['featured']);
        $this->assertEquals('value', $product->meta['custom']);
    }

    /** @test */
    public function it_can_query_by_meta_values()
    {
        Product::factory()->create(['meta' => ['featured' => true]]);
        Product::factory()->create(['meta' => ['featured' => false]]);

        $featured = Product::where('meta->featured', true)->get();

        $this->assertCount(1, $featured);
    }
}
```

## Troubleshooting

### Meta Not Set

**Problem**: Meta is null after creating model.

**Solutions**:
1. Check meta column exists and is JSON type
2. Ensure `$default_meta` property is defined
3. Add array cast: `'meta' => 'array'`

### Meta Not Updating

**Problem**: Meta changes not persisting.

**Solution**: Always save after modifying:

```php
$product->meta['key'] = 'value';
$product->save(); // Required
```

### Invalid JSON Error

**Problem**: Database rejects meta value.

**Solution**: Ensure column is JSON type:

```php
$table->json('meta')->nullable();
// NOT: $table->text('meta')
```

## Next Steps

- [InteractsWithTags](02-interacts-with-tags.md) - Manage JSON tag arrays
- [InteractsWithUser](07-interacts-with-user.md) - Auto-assign user IDs
- [Getting Started](../01-getting-started/03-basic-usage.md) - Basic trait usage
