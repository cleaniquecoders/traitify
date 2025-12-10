# InteractsWithDetails Trait

A Laravel trait that provides a convenient way to define and eager load common relationships, helping to reduce N+1 query problems.

## Overview

The InteractsWithDetails trait allows you to define a list of relationships that should be eager loaded when fetching detailed records, providing a standardized approach to relationship loading across your application.

## Installation

This trait is part of the `cleaniquecoders/traitify` package.

```bash
composer require cleaniquecoders/traitify
```

## Basic Usage

### 1. Add the Trait to Your Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use CleaniqueCoders\Traitify\Concerns\InteractsWithDetails;

class Post extends Model
{
    use InteractsWithDetails;

    protected $with_details = [
        'user',
        'comments',
        'tags',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
```

### 2. Use the withDetails() Scope

```php
// Load post with all defined relationships
$post = Post::withDetails()->find(1);

// Relationships are already loaded
echo $post->user->name; // No additional query
echo $post->comments->count(); // No additional query
```

## Features

### Define Common Relationships

Specify which relationships to load for detailed views:

```php
protected $with_details = [
    'user',
    'category',
    'tags',
    'comments.user',
];
```

### Eager Load Only When Needed

Unlike `$with`, relationships are only loaded when explicitly requested:

```php
// Without details - no relationships loaded
$posts = Post::all();

// With details - all specified relationships loaded
$posts = Post::withDetails()->get();
```

### Nested Relationships

Load nested relationships efficiently:

```php
protected $with_details = [
    'user',
    'comments',
    'comments.user',
    'comments.replies',
];
```

### Get Details List

Access the list of detail relationships:

```php
$relationships = $post->getDetails();
// Returns: ['user', 'comments', 'tags']
```

## Examples

### Blog Post with Details

```php
class Post extends Model
{
    use InteractsWithDetails;

    protected $with_details = [
        'author',
        'category',
        'tags',
        'comments',
        'comments.user',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}

// In controller
public function show(Post $post)
{
    $post->loadMissing($post->getDetails());

    return view('posts.show', compact('post'));
}

// Or use scope
public function show($id)
{
    $post = Post::withDetails()->findOrFail($id);

    return view('posts.show', compact('post'));
}
```

### Product with Relations

```php
class Product extends Model
{
    use InteractsWithDetails;

    protected $with_details = [
        'category',
        'brand',
        'images',
        'reviews',
        'reviews.user',
        'variants',
        'variants.options',
    ];
}

// Product detail page
$product = Product::withDetails()->find($id);
```

### User Profile

```php
class User extends Model
{
    use InteractsWithDetails;

    protected $with_details = [
        'profile',
        'posts',
        'posts.category',
        'comments',
        'followers',
        'following',
    ];
}

// User profile page
$user = User::withDetails()->findOrFail($username);
```

## Advanced Examples

### API Resource with Details

```php
class PostController extends Controller
{
    public function index()
    {
        $posts = Post::paginate(20);

        return PostResource::collection($posts);
    }

    public function show($id)
    {
        $post = Post::withDetails()->findOrFail($id);

        return new PostDetailResource($post);
    }
}

class PostDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => new UserResource($this->user),
            'category' => new CategoryResource($this->category),
            'tags' => TagResource::collection($this->tags),
            'comments' => CommentResource::collection($this->comments),
        ];
    }
}
```

### Conditional Details Loading

```php
class Post extends Model
{
    use InteractsWithDetails;

    protected $with_details = [
        'user',
        'category',
    ];

    public function scopeWithAllDetails($query)
    {
        return $query->withDetails()->with([
            'media',
            'seo',
            'related_posts',
        ]);
    }

    public function scopeWithPublicDetails($query)
    {
        return $query->with([
            'user:id,name,avatar',
            'category:id,name,slug',
            'tags:id,name',
        ]);
    }
}

// Use based on context
$post = Post::withAllDetails()->find($id); // Admin view
$post = Post::withPublicDetails()->find($id); // Public view
```

### Dynamic Details

```php
class Post extends Model
{
    use InteractsWithDetails;

    public function getDetails(): array
    {
        $details = ['user', 'category'];

        if (auth()->check()) {
            $details[] = 'bookmarks';
            $details[] = 'reading_progress';
        }

        if (auth()->user()?->isAdmin()) {
            $details[] = 'drafts';
            $details[] = 'analytics';
        }

        return $details;
    }
}
```

## Methods Reference

### Instance Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `getDetails()` | `array` | Get list of relationships to eager load |

### Query Scopes

| Scope | Parameters | Description |
|-------|-----------|-------------|
| `withDetails()` | - | Eager load all defined detail relationships |

## Performance Optimization

### Before (N+1 Problem)

```php
// This causes N+1 queries
$posts = Post::all(); // 1 query

foreach ($posts as $post) {
    echo $post->user->name; // N queries
    echo $post->category->name; // N queries
}
// Total: 1 + (N * 2) queries
```

### After (Using withDetails)

```php
// This executes only 3 queries
$posts = Post::withDetails()->get(); // 1 query + 2 eager load queries

foreach ($posts as $post) {
    echo $post->user->name; // No additional query
    echo $post->category->name; // No additional query
}
// Total: 3 queries
```

## Best Practices

### 1. Only Include Necessary Relationships

```php
// Good - specific to use case
protected $with_details = [
    'user:id,name,avatar',
    'category:id,name',
    'comments' => function($query) {
        $query->latest()->limit(5);
    },
];

// Avoid - loading too much
protected $with_details = [
    'user',
    'category',
    'tags',
    'comments',
    'likes',
    'shares',
    'bookmarks',
    'media',
    // ... too many
];
```

### 2. Use Select Columns for Performance

```php
protected $with_details = [
    'user:id,name,email,avatar',
    'category:id,name,slug',
];
```

### 3. Combine with Existing Eager Loading

```php
class Post extends Model
{
    // Always load these
    protected $with = ['user:id,name'];

    // Load these only for detail views
    protected $with_details = [
        'category',
        'tags',
        'comments',
    ];
}
```

### 4. Document Detail Relationships

```php
/**
 * Relationships loaded for detail views:
 * - user: Post author
 * - category: Post category
 * - tags: Associated tags
 * - comments: All comments with users
 */
protected $with_details = [
    'user',
    'category',
    'tags',
    'comments.user',
];
```

## Testing

Example test cases:

```php
use Tests\TestCase;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class InteractsWithDetailsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_detail_relationships()
    {
        $post = new Post();

        $details = $post->getDetails();

        $this->assertIsArray($details);
        $this->assertContains('user', $details);
        $this->assertContains('comments', $details);
    }

    /** @test */
    public function it_eager_loads_details()
    {
        Post::factory()
            ->has(Comment::factory()->count(3))
            ->create();

        DB::enableQueryLog();

        $post = Post::withDetails()->first();
        $commentCount = $post->comments->count();

        $queries = DB::getQueryLog();

        // Should be 2 queries: 1 for post, 1 for comments
        $this->assertCount(2, $queries);
        $this->assertEquals(3, $commentCount);
    }

    /** @test */
    public function it_prevents_n_plus_one_queries()
    {
        Post::factory()
            ->count(10)
            ->has(Comment::factory()->count(3))
            ->create();

        DB::enableQueryLog();

        $posts = Post::withDetails()->get();

        foreach ($posts as $post) {
            $post->comments->count();
        }

        $queries = DB::getQueryLog();

        // Should only have initial queries, not N+1
        $this->assertLessThan(15, count($queries));
    }
}
```

## Troubleshooting

### Relationships Not Loading

**Problem**: Relationships are still causing N+1 queries.

**Solutions**:
1. Ensure `withDetails()` scope is used
2. Check relationship names are correct
3. Verify relationships are defined on model

### Too Many Queries

**Problem**: Still seeing many queries even with withDetails.

**Solutions**:
1. Use Laravel Debugbar to identify queries
2. Add missing relationships to `$with_details`
3. Use `select()` to limit columns

### Memory Issues

**Problem**: Loading too much data causes memory errors.

**Solutions**:
1. Reduce number of relationships
2. Use pagination
3. Add query constraints:

```php
protected $with_details = [
    'comments' => function($query) {
        $query->limit(10);
    },
];
```

## Next Steps

- [InteractsWithApi](10-interacts-with-api.md) - API response formatting
- [InteractsWithSearchable](08-interacts-with-searchable.md) - Search functionality
- [Getting Started](../01-getting-started/03-basic-usage.md) - Basic trait usage
