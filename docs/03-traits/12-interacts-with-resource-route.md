# InteractsWithResourceRoute Trait

A Laravel trait that automatically generates resource route URLs based on your model's class name, following Laravel's naming conventions.

## Overview

The InteractsWithResourceRoute trait eliminates hardcoded route names by generating them automatically from your model's class name, making your code more maintainable and following Laravel conventions.

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
use CleaniqueCoders\Traitify\Concerns\InteractsWithResourceRoute;

class Post extends Model
{
    use InteractsWithResourceRoute;
}
```

### 2. Define Your Routes

```php
// routes/web.php
Route::resource('posts', PostController::class);
```

### 3. Generate Route URLs

```php
$post = Post::find(1);

// Get route URLs
$indexUrl = $post->getResourceUrl('index');    // /posts
$showUrl = $post->getResourceUrl('show');      // /posts/1
$editUrl = $post->getResourceUrl('edit');      // /posts/1/edit
$createUrl = $post->getResourceUrl('create');  // /posts/create
```

## Features

### Automatic Route Name Generation

The trait automatically converts your model class name to the appropriate route name:

```php
// Post model → posts.index, posts.show, etc.
$post->getResourceUrl('index');

// Product model → products.index, products.show, etc.
$product->getResourceUrl('show');
```

### Supported Resource Actions

```php
$model->getResourceUrl('index');   // Index page
$model->getResourceUrl('create');  // Create form
$model->getResourceUrl('store');   // Store endpoint (same as index for GET)
$model->getResourceUrl('show');    // Show page (requires model ID)
$model->getResourceUrl('edit');    // Edit form (requires model ID)
$model->getResourceUrl('update');  // Update endpoint (same as show for GET)
$model->getResourceUrl('destroy'); // Delete endpoint (same as show for GET)
```

### Route Prefix Support

Add a prefix to your resource routes:

```php
class Post extends Model
{
    use InteractsWithResourceRoute;

    protected $url_route_prefix = 'admin';
}

$post->getResourceUrl('index'); // /admin/posts
```

### Custom Route Base Name

Override the auto-generated route base name:

```php
public function getUrlRouteBaseName(): string
{
    return 'articles'; // Instead of 'posts'
}

$post->getResourceUrl('index'); // /articles
```

## Examples

### Simple Blog Posts

```php
class Post extends Model
{
    use InteractsWithResourceRoute;
}

// In blade views
<a href="{{ $post->getResourceUrl('show') }}">View Post</a>
<a href="{{ $post->getResourceUrl('edit') }}">Edit Post</a>

// In controllers
return redirect($post->getResourceUrl('index'));
```

### Admin Panel with Prefix

```php
class Product extends Model
{
    use InteractsWithResourceRoute;

    protected $url_route_prefix = 'admin';
}

// Routes
Route::prefix('admin')->group(function () {
    Route::resource('products', ProductController::class);
});

// Usage
$product->getResourceUrl('index');  // /admin/products
$product->getResourceUrl('edit');   // /admin/products/1/edit
```

### API Resources

```php
class ApiPost extends Model
{
    use InteractsWithResourceRoute;

    protected $url_route_prefix = 'api/v1';

    public function getUrlRouteBaseName(): string
    {
        return 'posts';
    }
}

// Routes
Route::prefix('api/v1')->group(function () {
    Route::apiResource('posts', ApiPostController::class);
});

// Usage
$post->getResourceUrl('index');  // /api/v1/posts
$post->getResourceUrl('show');   // /api/v1/posts/1
```

### Nested Resources

```php
class Comment extends Model
{
    use InteractsWithResourceRoute;

    public function getResourceUrl(string $type = 'index', array $parameters = []): string
    {
        $postId = $parameters['post_id'] ?? $this->post_id;

        return route("posts.comments.{$type}", array_merge(
            ['post' => $postId],
            $type !== 'index' && $type !== 'create' ? [$this->id] : [],
            $parameters
        ));
    }
}

// Routes
Route::resource('posts.comments', CommentController::class);

// Usage
$comment->getResourceUrl('show', ['post_id' => 1]); // /posts/1/comments/1
```

## Advanced Examples

### Multi-Word Model Names

```php
class BlogPost extends Model
{
    use InteractsWithResourceRoute;
}

// Automatically converts to blog-posts
$blogPost->getResourceUrl('index'); // /blog-posts
```

### Custom Pluralization

```php
class Person extends Model
{
    use InteractsWithResourceRoute;

    public function getUrlRouteBaseName(): string
    {
        return 'people'; // Instead of 'persons'
    }
}

$person->getResourceUrl('index'); // /people
```

### Different Route Names for Different Contexts

```php
class Post extends Model
{
    use InteractsWithResourceRoute;

    public function getAdminUrl(string $action): string
    {
        $this->url_route_prefix = 'admin';
        return $this->getResourceUrl($action);
    }

    public function getPublicUrl(string $action): string
    {
        $this->url_route_prefix = null;
        return $this->getResourceUrl($action);
    }
}

// Usage
$post->getAdminUrl('edit');  // /admin/posts/1/edit
$post->getPublicUrl('show'); // /posts/1
```

## Usage in Views

### Blade Templates

```blade
{{-- Post list --}}
@foreach($posts as $post)
    <article>
        <h2>
            <a href="{{ $post->getResourceUrl('show') }}">
                {{ $post->title }}
            </a>
        </h2>
        <div>
            <a href="{{ $post->getResourceUrl('edit') }}">Edit</a>
            <form action="{{ $post->getResourceUrl('destroy') }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit">Delete</button>
            </form>
        </div>
    </article>
@endforeach

{{-- Create button --}}
<a href="{{ (new Post)->getResourceUrl('create') }}">
    Create New Post
</a>
```

### Navigation Menus

```blade
<nav>
    <a href="{{ (new Post)->getResourceUrl('index') }}">Posts</a>
    <a href="{{ (new Product)->getResourceUrl('index') }}">Products</a>
    <a href="{{ (new User)->getResourceUrl('index') }}">Users</a>
</nav>
```

## Methods Reference

### Instance Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `getResourceUrl($type)` | `string` | Generate resource URL for given type |
| `getUrlRouteBaseName()` | `string` | Get base route name (override to customize) |
| `getUrlRoutePrefix()` | `string\|null` | Get route prefix if set |

### Supported Types

- `index` - List all resources
- `create` - Show create form
- `store` - Store new resource (uses index route)
- `show` - Show single resource
- `edit` - Show edit form
- `update` - Update resource (uses show route)
- `destroy` - Delete resource (uses show route)

## Best Practices

### 1. Follow Laravel Resource Naming

```php
// Good - follows convention
Route::resource('posts', PostController::class);

// Model will auto-generate correct routes
$post->getResourceUrl('index'); // Works perfectly
```

### 2. Use for Links, Not Business Logic

```php
// Good - for generating links
<a href="{{ $post->getResourceUrl('edit') }}">Edit</a>

// Avoid - for business logic (use direct route() instead)
return redirect($post->getResourceUrl('index'));
```

### 3. Combine with Route Model Binding

```php
Route::resource('posts', PostController::class);

// Controller
public function show(Post $post)
{
    return view('posts.show', compact('post'));
}

// View
<a href="{{ $post->getResourceUrl('edit') }}">Edit</a>
```

### 4. Cache Route Names

```php
class Post extends Model
{
    use InteractsWithResourceRoute;

    protected $urlRouteBaseName;

    public function getUrlRouteBaseName(): string
    {
        if (!$this->urlRouteBaseName) {
            $this->urlRouteBaseName = parent::getUrlRouteBaseName();
        }

        return $this->urlRouteBaseName;
    }
}
```

## Testing

Example test cases:

```php
use Tests\TestCase;
use App\Models\Post;

class InteractsWithResourceRouteTest extends TestCase
{
    /** @test */
    public function it_generates_index_url()
    {
        $post = new Post();

        $url = $post->getResourceUrl('index');

        $this->assertEquals(route('posts.index'), $url);
    }

    /** @test */
    public function it_generates_show_url()
    {
        $post = Post::factory()->create();

        $url = $post->getResourceUrl('show');

        $this->assertEquals(route('posts.show', $post), $url);
    }

    /** @test */
    public function it_respects_route_prefix()
    {
        $post = new class extends Post {
            protected $url_route_prefix = 'admin';
        };

        $url = $post->getResourceUrl('index');

        $this->assertEquals(route('admin.posts.index'), $url);
    }
}
```

## Troubleshooting

### Route Not Found Error

**Problem**: Route [posts.index] not defined.

**Solutions**:
1. Ensure routes are defined:
```php
Route::resource('posts', PostController::class);
```
2. Check route name matches model:
```php
php artisan route:list | grep posts
```

### Wrong Route Generated

**Problem**: Generated route doesn't match expectations.

**Solutions**:
1. Override `getUrlRouteBaseName()`:
```php
public function getUrlRouteBaseName(): string
{
    return 'articles';
}
```
2. Check route naming in routes file

### Prefix Not Working

**Problem**: Prefix not added to routes.

**Solution**: Ensure routes have prefix:

```php
Route::prefix('admin')->group(function () {
    Route::resource('posts', PostController::class);
});
```

## Next Steps

- [Getting Started](../01-getting-started/README.md) - Basic usage
- [Examples](../06-examples/README.md) - Real-world examples
