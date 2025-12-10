# InteractsWithApi Trait

A Laravel trait for standardizing API response formatting in your JSON resources with consistent structure for data, messages, and status codes.

## Overview

The InteractsWithApi trait provides a consistent API response format across your application, making it easier to build standardized REST APIs.

## Installation

This trait is part of the `cleaniquecoders/traitify` package.

```bash
composer require cleaniquecoders/traitify
```

## Basic Usage

### 1. Add the Trait to Your Resource

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use CleaniqueCoders\Traitify\Concerns\InteractsWithApi;
use CleaniqueCoders\Traitify\Contracts\Api;

class PostResource extends JsonResource implements Api
{
    use InteractsWithApi;

    public function getData($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'created_at' => $this->created_at,
        ];
    }
}
```

### 2. Use in Controller

```php
use App\Http\Resources\PostResource;

class PostController extends Controller
{
    public function show(Post $post)
    {
        return new PostResource($post);
    }
}
```

### 3. Response Format

```json
{
    "data": {
        "id": 1,
        "title": "My Post",
        "content": "Post content here",
        "created_at": "2024-01-15T10:30:00.000000Z"
    },
    "message": "",
    "code": 200
}
```

## Features

### Standardized Response Structure

All API responses follow a consistent format:

```php
{
    "data": { },      // Your resource data
    "message": "",    // Optional message
    "code": 200       // HTTP status code
}
```

### Customizable Messages

Add custom messages to responses:

```php
class PostResource extends JsonResource implements Api
{
    use InteractsWithApi;

    public function getData($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
        ];
    }

    public function getMessage()
    {
        return 'Post retrieved successfully';
    }
}
```

Response:
```json
{
    "data": { "id": 1, "title": "My Post" },
    "message": "Post retrieved successfully",
    "code": 200
}
```

### Custom Status Codes

Set custom HTTP status codes:

```php
class PostResource extends JsonResource implements Api
{
    use InteractsWithApi;

    protected $code = 201; // Created

    public function getData($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
        ];
    }

    public function getMessage()
    {
        return 'Post created successfully';
    }
}
```

## Examples

### Simple Resource

```php
class UserResource extends JsonResource implements Api
{
    use InteractsWithApi;

    public function getData($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
```

### Resource with Relationships

```php
class PostResource extends JsonResource implements Api
{
    use InteractsWithApi;

    public function getData($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => new UserResource($this->whenLoaded('user')),
            'comments_count' => $this->comments_count,
        ];
    }
}
```

### Collection Resource

```php
class PostCollection extends ResourceCollection implements Api
{
    use InteractsWithApi;

    public function getData($request)
    {
        return [
            'posts' => PostResource::collection($this->collection),
            'total' => $this->total(),
            'per_page' => $this->perPage(),
        ];
    }

    public function getMessage()
    {
        return sprintf('Retrieved %d posts', $this->count());
    }
}
```

### Conditional Data

```php
class PostResource extends JsonResource implements Api
{
    use InteractsWithApi;

    public function getData($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->when(
                $request->user()->can('view', $this->resource),
                $this->content
            ),
            'admin_notes' => $this->when(
                $request->user()->isAdmin(),
                $this->admin_notes
            ),
        ];
    }
}
```

## Advanced Examples

### Error Responses

```php
class ErrorResource extends JsonResource implements Api
{
    use InteractsWithApi;

    protected $code = 422;

    public function getData($request)
    {
        return [
            'errors' => $this->resource,
        ];
    }

    public function getMessage()
    {
        return 'Validation failed';
    }
}

// Usage
public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'title' => 'required',
    ]);

    if ($validator->fails()) {
        return (new ErrorResource($validator->errors()))
            ->response()
            ->setStatusCode(422);
    }

    // ...
}
```

### Success with Different Status Codes

```php
class PostCreatedResource extends PostResource
{
    protected $code = 201;

    public function getMessage()
    {
        return 'Post created successfully';
    }
}

class PostUpdatedResource extends PostResource
{
    protected $code = 200;

    public function getMessage()
    {
        return 'Post updated successfully';
    }
}

class PostDeletedResource extends JsonResource implements Api
{
    use InteractsWithApi;

    protected $code = 204;

    public function getData($request)
    {
        return [];
    }

    public function getMessage()
    {
        return 'Post deleted successfully';
    }
}
```

### Paginated Responses

```php
class PostCollection extends ResourceCollection implements Api
{
    use InteractsWithApi;

    public function getData($request)
    {
        return [
            'posts' => PostResource::collection($this->collection),
            'pagination' => [
                'total' => $this->total(),
                'count' => $this->count(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'total_pages' => $this->lastPage(),
                'links' => [
                    'next' => $this->nextPageUrl(),
                    'prev' => $this->previousPageUrl(),
                ],
            ],
        ];
    }
}
```

### Nested Resources

```php
class PostDetailResource extends JsonResource implements Api
{
    use InteractsWithApi;

    public function getData($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'author' => new UserResource($this->user),
            'category' => new CategoryResource($this->category),
            'tags' => TagResource::collection($this->tags),
            'comments' => CommentResource::collection($this->comments),
            'meta' => [
                'views' => $this->views_count,
                'likes' => $this->likes_count,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
        ];
    }
}
```

## Methods Reference

### Required Methods (from Api Contract)

| Method | Returns | Description |
|--------|---------|-------------|
| `getData($request)` | `array` | Returns the data portion of response |
| `getMessage()` | `string` | Returns the message (default: empty string) |
| `getCode()` | `int` | Returns HTTP status code (default: 200) |

### Provided Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `getApiResponse($request)` | `array` | Returns complete API response structure |

## Response Structure

The trait ensures all responses follow this structure:

```json
{
    "data": {
        // Your resource data from getData()
    },
    "message": "Optional message from getMessage()",
    "code": 200 // HTTP status code from getCode()
}
```

## Best Practices

### 1. Implement the Api Contract

```php
use CleaniqueCoders\Traitify\Contracts\Api;

class PostResource extends JsonResource implements Api
{
    use InteractsWithApi;

    // Implement required methods
}
```

### 2. Use Semantic Status Codes

```php
// 200 - OK (GET, PUT, PATCH)
protected $code = 200;

// 201 - Created (POST)
protected $code = 201;

// 204 - No Content (DELETE)
protected $code = 204;

// 422 - Validation Error
protected $code = 422;
```

### 3. Provide Clear Messages

```php
public function getMessage()
{
    return 'Resource retrieved successfully';
}

// Not just:
public function getMessage()
{
    return 'Success';
}
```

### 4. Keep Data Clean

```php
public function getData($request)
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        // Only include relevant data
    ];
}
```

## Testing

Example test cases:

```php
use Tests\TestCase;
use App\Models\Post;
use App\Http\Resources\PostResource;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InteractsWithApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_standardized_api_response()
    {
        $post = Post::factory()->create();

        $response = $this->getJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'title', 'content'],
                'message',
                'code',
            ]);
    }

    /** @test */
    public function it_includes_custom_message()
    {
        $post = Post::factory()->create();

        $response = $this->getJson("/api/posts/{$post->id}");

        $response->assertJson([
            'message' => 'Post retrieved successfully',
        ]);
    }

    /** @test */
    public function it_returns_correct_status_code()
    {
        $data = ['title' => 'New Post', 'content' => 'Content'];

        $response = $this->postJson('/api/posts', $data);

        $response->assertStatus(201)
            ->assertJson([
                'code' => 201,
                'message' => 'Post created successfully',
            ]);
    }
}
```

## Troubleshooting

### Missing getData Method

**Problem**: Class must implement getData method.

**Solution**: Ensure you implement the Api contract:

```php
class PostResource extends JsonResource implements Api
{
    use InteractsWithApi;

    public function getData($request)
    {
        return [/* your data */];
    }
}
```

### Incorrect Response Format

**Problem**: Response doesn't include data/message/code.

**Solution**: Return the resource properly:

```php
// Correct
return new PostResource($post);

// Incorrect
return $post;
```

## Next Steps

- [InteractsWithResourceRoute](11-interacts-with-resource-route.md) - Resource URL generation
- [Examples](../06-examples/README.md) - Real-world API examples
- [Getting Started](../01-getting-started/README.md) - Basic usage
