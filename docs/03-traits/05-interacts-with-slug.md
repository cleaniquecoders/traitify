# InteractsWithSlug Trait

A Laravel trait that automatically generates SEO-friendly URL slugs from your model attributes with support for uniqueness, transliteration, and extensive customization.

## Overview

The InteractsWithSlug trait provides automatic slug generation from a source column (default: 'name'), supports regeneration on updates, and offers extensive configuration for SEO optimization.

## Installation

This trait is part of the `cleaniquecoders/traitify` package.

```bash
composer require cleaniquecoders/traitify
```

## Database Setup

Add a slug column to your table migration:

```php
Schema::create('articles', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('content');
    $table->timestamps();
});
```

## Basic Usage

### 1. Add the Trait to Your Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use CleaniqueCoders\Traitify\Concerns\InteractsWithSlug;

class Article extends Model
{
    use InteractsWithSlug;

    protected $fillable = ['name', 'content'];
}
```

### 2. Slugs are Auto-Generated

```php
$article = Article::create(['name' => 'Getting Started with Laravel']);
echo $article->slug; // getting-started-with-laravel
```

## Features

### Automatic Slug Generation

Slugs are automatically generated from the source column on create:

```php
$article = new Article(['name' => 'Hello World!']);
$article->save();
echo $article->slug; // hello-world
```

### Auto-Regeneration on Update

Slugs are regenerated when the source column changes:

```php
$article->update(['name' => 'New Title']);
echo $article->slug; // new-title
```

### Query Scope

Find models by slug using the query scope:

```php
$article = Article::slug('getting-started-with-laravel')->first();
```

### Custom Column Names

Override default column names:

```php
class Article extends Model
{
    use InteractsWithSlug;

    protected $slug_column = 'permalink';
    protected $slug_source_column = 'title';
}
```

## Configuration Options

### Separator

Change the word separator (default: '-'):

```php
protected $slugGeneratorConfig = [
    'separator' => '_',
];
// Result: getting_started_with_laravel
```

### Language/Transliteration

Set language for proper transliteration:

```php
protected $slugGeneratorConfig = [
    'language' => 'de', // German
];
// "Übersetzen" becomes "ubersetzen"
```

### Lowercase

Control case conversion (default: true):

```php
protected $slugGeneratorConfig = [
    'lowercase' => false,
];
// Result: Getting-Started-With-Laravel
```

### Maximum Length

Limit slug length:

```php
protected $slugGeneratorConfig = [
    'max_length' => 50,
];
```

### Unique Slugs

Automatically append numbers for uniqueness:

```php
protected $slugGeneratorConfig = [
    'unique' => true,
];
// First:  hello-world
// Second: hello-world-2
// Third:  hello-world-3
```

### Prefix and Suffix

Add prefix or suffix to slugs:

```php
protected $slugGeneratorConfig = [
    'prefix' => 'article-',
    'suffix' => '-2024',
];
// Result: article-hello-world-2024
```

### Custom Dictionary

Define custom character replacements:

```php
protected $slugGeneratorConfig = [
    'dictionary' => [
        '@' => 'at',
        '&' => 'and',
        '#' => 'number',
    ],
];
// "Contact @ Company" becomes "contact-at-company"
```

## App-Wide Configuration

Configure slugs globally in `config/traitify.php`:

```php
'generators' => [
    'slug' => [
        'class' => \CleaniqueCoders\Traitify\Generators\SlugGenerator::class,
        'config' => [
            'separator' => '-',
            'lowercase' => true,
            'unique' => false,
        ],
    ],
],
```

## Common Use Cases

### Blog Posts

```php
class Post extends Model
{
    use InteractsWithSlug;

    protected $slug_source_column = 'title';

    protected $slugGeneratorConfig = [
        'unique' => true,
        'max_length' => 100,
    ];
}
```

### Products (SKU-style)

```php
class Product extends Model
{
    use InteractsWithSlug;

    protected $slug_column = 'sku';

    protected $slugGeneratorConfig = [
        'separator' => '_',
        'uppercase' => true,
        'prefix' => 'PRD-',
    ];
}
// Result: PRD-WIDGET_NAME
```

### Categories (Hierarchical)

```php
class Category extends Model
{
    use InteractsWithSlug;

    protected $slugGeneratorConfig = [
        'unique' => true,
    ];

    public function getFullSlug(): string
    {
        if ($this->parent) {
            return $this->parent->getFullSlug() . '/' . $this->slug;
        }

        return $this->slug;
    }
}
```

### User Profiles

```php
class User extends Model
{
    use InteractsWithSlug;

    protected $slug_source_column = 'username';
    protected $slug_column = 'profile_slug';

    protected $slugGeneratorConfig = [
        'unique' => true,
        'lowercase' => true,
    ];
}
```

### Multi-Language Content

```php
class Article extends Model
{
    use InteractsWithSlug;

    protected $slugGeneratorConfig = [
        'language' => 'de',
        'dictionary' => [
            'ä' => 'ae',
            'ö' => 'oe',
            'ü' => 'ue',
            'ß' => 'ss',
        ],
    ];
}
```

## Advanced Examples

### Conditional Slug Source

```php
class Post extends Model
{
    use InteractsWithSlug;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            $source = $post->seo_title ?? $post->title;
            $post->setAttribute($post->getSlugSourceColumnName(), $source);
        });
    }
}
```

### Manual Slug Override

```php
// Slugs won't be overwritten if manually set
$article = Article::create([
    'name' => 'Example Article',
    'slug' => 'my-custom-slug',
]);
// Will keep 'my-custom-slug'
```

### Date-Based Slugs

```php
class Post extends Model
{
    use InteractsWithSlug;

    protected $slugGeneratorConfig = [
        'suffix' => function ($model) {
            return $model->created_at->format('Y-m-d');
        },
    ];
}
// Result: my-post-2024-01-15
```

## Methods Reference

### Instance Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `getSlugColumnName()` | `string` | Get the slug column name |
| `getSlugSourceColumnName()` | `string` | Get the source column name for slug |

### Query Scopes

| Scope | Parameters | Description |
|-------|-----------|-------------|
| `slug($value)` | `string` | Find model by slug value |

## SEO Best Practices

### 1. Keep Slugs Short and Descriptive

```php
// Good
protected $slugGeneratorConfig = [
    'max_length' => 60,
];

// Too long - can be cut off in search results
protected $slugGeneratorConfig = [
    'max_length' => 200,
];
```

### 2. Use Hyphens, Not Underscores

```php
// Preferred for SEO
protected $slugGeneratorConfig = [
    'separator' => '-',
];

// Less optimal
protected $slugGeneratorConfig = [
    'separator' => '_',
];
```

### 3. Always Lowercase

```php
protected $slugGeneratorConfig = [
    'lowercase' => true, // Consistent URLs
];
```

### 4. Enable Uniqueness for Content

```php
protected $slugGeneratorConfig = [
    'unique' => true, // Avoid duplicate content issues
];
```

## Testing

Example test cases:

```php
use Tests\TestCase;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InteractsWithSlugTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_generates_slug_from_name()
    {
        $article = Article::factory()->create([
            'name' => 'Getting Started with Laravel',
        ]);

        $this->assertEquals('getting-started-with-laravel', $article->slug);
    }

    /** @test */
    public function it_regenerates_slug_on_name_change()
    {
        $article = Article::factory()->create(['name' => 'Original']);

        $article->update(['name' => 'Updated Title']);

        $this->assertEquals('updated-title', $article->slug);
    }

    /** @test */
    public function it_can_find_by_slug()
    {
        $article = Article::factory()->create();

        $found = Article::slug($article->slug)->first();

        $this->assertTrue($found->is($article));
    }

    /** @test */
    public function it_creates_unique_slugs()
    {
        $model = new class extends Article {
            protected $slugGeneratorConfig = ['unique' => true];
        };

        $first = $model->create(['name' => 'Test']);
        $second = $model->create(['name' => 'Test']);

        $this->assertEquals('test', $first->slug);
        $this->assertEquals('test-2', $second->slug);
    }
}
```

## Troubleshooting

### Slug Not Generated

**Problem**: Slug is null after creating model.

**Solutions**:
1. Check slug column exists in database
2. Check source column has a value
3. Ensure column names match (default: slug/name)
4. Clear config cache: `php artisan config:clear`

### Slug Not Updating

**Problem**: Slug doesn't change when source column is updated.

**Check**: The trait only regenerates on update if:
1. Source column value actually changed
2. Slug column is not manually set in the update

### Special Characters Not Converting

**Problem**: Special characters not handled properly.

**Solution**: Add custom dictionary:

```php
protected $slugGeneratorConfig = [
    'dictionary' => [
        '€' => 'euro',
        '£' => 'pound',
        // Add more as needed
    ],
];
```

### Route Model Binding with Slugs

To use slugs for route model binding:

```php
public function getRouteKeyName()
{
    return 'slug';
}
```

## Next Steps

- [InteractsWithUuid](03-interacts-with-uuid.md) - Generate UUIDs
- [InteractsWithToken](04-interacts-with-token.md) - Generate tokens
- [Generators](../04-generators/01-overview.md) - Learn about slug generator options
