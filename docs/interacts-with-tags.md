# InteractsWithTags Trait

A Laravel trait that provides a fluent interface for managing tags stored as JSON in your database models.

## Installation

This trait is part of the `cleaniquecoders/traitify` package.

```bash
composer require cleaniquecoders/traitify
```

## Requirements

- PHP 8.0 or higher
- Laravel 9.x or higher
- Database with JSON column support (MySQL 5.7+, PostgreSQL 9.5+, SQLite 3.9+)

## Database Setup

Add a JSON column to your table migration:

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->json('tags')->nullable();
    $table->timestamps();
});
```

## Basic Usage

### 1. Add the Trait to Your Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use CleaniqueCoders\Traitify\Concerns\InteractsWithTags;

class Post extends Model
{
    use InteractsWithTags;
    
    protected $fillable = ['title', 'tags'];
}
```

### 2. Customize Column Name (Optional)

By default, the trait uses `tags` as the column name. You can customize it:

```php
class Post extends Model
{
    use InteractsWithTags;
    
    protected $tags_column = 'categories'; // Custom column name
}
```

## Features

### Setting Tags

Set tags for a model (replaces existing tags):

```php
// Using array
$post->setTags(['laravel', 'php', 'tutorial']);

// Using comma-separated string
$post->setTags('laravel, php, tutorial');

// Don't forget to save
$post->save();

// Or chain it
$post->setTags(['laravel', 'php'])->save();
```

### Adding Tags

Add tags without removing existing ones:

```php
$post->addTags(['vue', 'javascript'])->save();

// String format
$post->addTags('vue, javascript')->save();

// Duplicates are automatically removed
$post->addTags(['laravel'])->save(); // Won't add duplicate 'laravel'
```

### Removing Tags

Remove specific tags:

```php
$post->removeTags(['php'])->save();

// String format
$post->removeTags('php, tutorial')->save();

// Multiple tags
$post->removeTags(['php', 'tutorial'])->save();
```

### Clearing All Tags

Remove all tags from a model:

```php
$post->clearTags()->save();
```

### Getting Tags

Retrieve tags as an array:

```php
$tags = $post->getTags();
// Returns: ['laravel', 'php', 'tutorial']
```

### Checking Tags

Check if a model has specific tags:

```php
// Check if has any of the given tags
$post->hasTag('laravel'); // true
$post->hasTag(['laravel', 'vue']); // true if has either

// Check if has all of the given tags
$post->hasAllTags(['laravel', 'php']); // true only if has both
```

## Query Scopes

### With Any Tags

Find models that have at least one of the specified tags:

```php
// Posts with 'laravel' OR 'php'
$posts = Post::withAnyTags(['laravel', 'php'])->get();

// String format
$posts = Post::withAnyTags('laravel, php')->get();
```

### With All Tags

Find models that have all of the specified tags:

```php
// Posts with 'laravel' AND 'php'
$posts = Post::withAllTags(['laravel', 'php'])->get();

// String format
$posts = Post::withAllTags('laravel, php')->get();
```

### Without Tags

Find models that don't have any of the specified tags:

```php
// Posts without 'draft' AND without 'archived'
$posts = Post::withoutTags(['draft', 'archived'])->get();

// String format
$posts = Post::withoutTags('draft, archived')->get();
```

### Without Any Tags (Alternative)

Find models that don't have any of the specified tags (different implementation):

```php
$posts = Post::withoutAnyTags(['draft', 'archived'])->get();
```

### Has Tags

Find models that have at least one tag:

```php
$posts = Post::hasTags()->get();
```

### Has No Tags

Find models that have no tags:

```php
$posts = Post::hasNoTags()->get();
```

## Advanced Examples

### Combining Scopes

```php
// Posts with 'laravel' tag but without 'draft' tag
$posts = Post::withAnyTags(['laravel'])
    ->withoutTags(['draft'])
    ->get();

// Published posts with specific tags
$posts = Post::where('status', 'published')
    ->withAllTags(['laravel', 'tutorial'])
    ->hasTags()
    ->get();
```

### Mass Assignment

```php
// Create with tags
$post = Post::create([
    'title' => 'My Laravel Tutorial',
    'tags' => ['laravel', 'php', 'tutorial']
]);

// Update with tags
$post->update([
    'tags' => ['laravel', 'vue', 'javascript']
]);
```

### Working with Forms

```php
// In your controller
public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string',
        'tags' => 'nullable|string', // or 'array'
    ]);
    
    $post = Post::create([
        'title' => $validated['title'],
    ]);
    
    // Tags from comma-separated input
    $post->setTags($validated['tags'])->save();
    
    return redirect()->route('posts.show', $post);
}
```

### API Resources

```php
class PostResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'tags' => $this->getTags(),
            'created_at' => $this->created_at,
        ];
    }
}
```

## Methods Reference

### Instance Methods

| Method | Parameters | Returns | Description |
|--------|-----------|---------|-------------|
| `getTagsColumnName()` | - | `string` | Get the tags column name |
| `getTags()` | - | `array` | Get all tags as array |
| `setTags($tags)` | `mixed` | `self` | Set tags (replaces existing) |
| `addTags($tags)` | `mixed` | `self` | Add tags to existing |
| `removeTags($tags)` | `mixed` | `self` | Remove specific tags |
| `clearTags()` | - | `self` | Remove all tags |
| `hasTag($tags)` | `mixed` | `bool` | Check if has any tag |
| `hasAllTags($tags)` | `mixed` | `bool` | Check if has all tags |

### Query Scopes

| Scope | Parameters | Description |
|-------|-----------|-------------|
| `withAnyTags($tags)` | `mixed` | Models with any of the tags |
| `withAllTags($tags)` | `mixed` | Models with all of the tags |
| `withoutTags($tags)` | `mixed` | Models without all of the tags |
| `withoutAnyTags($tags)` | `mixed` | Models without any of the tags |
| `hasTags()` | - | Models that have tags |
| `hasNoTags()` | - | Models that have no tags |

## Input Formats

The trait accepts tags in multiple formats:

```php
// Array
$post->setTags(['laravel', 'php', 'tutorial']);

// Comma-separated string
$post->setTags('laravel, php, tutorial');

// Single string
$post->setTags('laravel');

// Null (clears tags)
$post->setTags(null);
```

## Database Support

The trait uses Laravel's JSON query methods, which work with:

- **MySQL 5.7+**: Uses `JSON_CONTAINS()`
- **PostgreSQL 9.5+**: Uses `jsonb` operators
- **SQLite 3.9+**: Uses JSON functions
- **SQL Server 2016+**: Uses `OPENJSON()`

## Performance Considerations

### Indexing

For better query performance on large datasets, consider adding indexes:

**MySQL:**
```sql
ALTER TABLE posts ADD INDEX idx_tags ((CAST(tags AS CHAR(255) ARRAY)));
```

**PostgreSQL:**
```sql
CREATE INDEX idx_tags ON posts USING GIN (tags);
```

### Caching

For frequently accessed tags, consider caching:

```php
use Illuminate\Support\Facades\Cache;

public function getPopularTags()
{
    return Cache::remember('popular_tags', 3600, function () {
        return Post::all()
            ->pluck('tags')
            ->flatten()
            ->countBy()
            ->sortDesc()
            ->take(10);
    });
}
```

## Testing

Example test cases:

```php
use Tests\TestCase;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InteractsWithTagsTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function it_can_set_tags()
    {
        $post = Post::factory()->create();
        $post->setTags(['laravel', 'php'])->save();
        
        $this->assertEquals(['laravel', 'php'], $post->getTags());
    }
    
    /** @test */
    public function it_can_add_tags()
    {
        $post = Post::factory()->create();
        $post->setTags(['laravel'])->save();
        $post->addTags(['php'])->save();
        
        $this->assertCount(2, $post->getTags());
        $this->assertTrue($post->hasAllTags(['laravel', 'php']));
    }
    
    /** @test */
    public function it_can_query_by_tags()
    {
        Post::factory()->create()->setTags(['laravel'])->save();
        Post::factory()->create()->setTags(['php'])->save();
        Post::factory()->create()->setTags(['laravel', 'php'])->save();
        
        $posts = Post::withAnyTags(['laravel'])->get();
        
        $this->assertCount(2, $posts);
    }
}
```

## Troubleshooting

### Tags Not Saving

Make sure the column is in your `$fillable` array or `$guarded` is empty:

```php
protected $fillable = ['title', 'tags'];
```

### JSON Column Not Working

Ensure your database supports JSON and the column type is correct:

```php
// In migration
$table->json('tags')->nullable();

// Not text or string
// $table->text('tags'); // ❌ Wrong
```

### Query Not Finding Records

Check that you're using the correct scope:

```php
// Use withAnyTags for OR condition
Post::withAnyTags(['laravel', 'php']); // ✅ Has either

// Use withAllTags for AND condition
Post::withAllTags(['laravel', 'php']); // ✅ Has both
```

If you discover any issues, please email support@cleaniquecoders.com or create an issue in the GitHub repository.
