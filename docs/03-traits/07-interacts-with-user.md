# InteractsWithUser Trait

A Laravel trait that automatically assigns the authenticated user's ID to models when they are created.

## Overview

The InteractsWithUser trait eliminates boilerplate code for tracking which user created a record by automatically populating a user_id column with the currently authenticated user's ID.

## Installation

This trait is part of the `cleaniquecoders/traitify` package.

```bash
composer require cleaniquecoders/traitify
```

## Database Setup

Add a user_id column to your table migration:

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('title');
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
use CleaniqueCoders\Traitify\Concerns\InteractsWithUser;

class Post extends Model
{
    use InteractsWithUser;

    protected $fillable = ['title', 'content'];
}
```

### 2. User ID is Auto-Assigned

```php
// Assuming user is authenticated
Auth::loginUsingId(1);

$post = Post::create(['title' => 'My Post', 'content' => '...']);

echo $post->user_id; // 1 (automatically set)
```

## Features

### Automatic User Assignment

The user_id is automatically set when creating models:

```php
$post = new Post(['title' => 'Example']);
$post->save();
// user_id is automatically assigned from Auth::id()
```

### Respects Manual Assignment

If you manually set a user_id, it won't be overwritten:

```php
$post = Post::create([
    'title' => 'Admin Post',
    'user_id' => 5, // Manually assigned
]);
// Will keep user_id = 5
```

### Only Works When Authenticated

If no user is authenticated, the field remains null:

```php
Auth::logout();

$post = Post::create(['title' => 'Anonymous Post']);
echo $post->user_id; // null
```

### Custom Column Name

Override the default user_id column name:

```php
class Post extends Model
{
    use InteractsWithUser;

    protected $user_id_column = 'author_id';
}
```

## Common Use Cases

### Blog Posts

```php
class Post extends Model
{
    use InteractsWithUser;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

// Create post
$post = Post::create(['title' => 'My Article']);

// Access creator
echo $post->user->name;
```

### Comments

```php
class Comment extends Model
{
    use InteractsWithUser;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}

$comment = Comment::create([
    'post_id' => 1,
    'content' => 'Great article!',
]);
// user_id automatically set
```

### Audit Tracking

```php
class AuditLog extends Model
{
    use InteractsWithUser;

    protected $user_id_column = 'performed_by';

    public function user()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
```

### Multi-User Tracking

```php
class Document extends Model
{
    use InteractsWithUser; // For created_by

    protected $user_id_column = 'created_by';

    protected $fillable = ['title', 'content', 'updated_by'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($document) {
            $document->updated_by = auth()->id();
        });
    }
}
```

## Advanced Examples

### Admin Override

Allow admins to create content as other users:

```php
class Post extends Model
{
    use InteractsWithUser;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            // Allow admins to override user_id
            if (auth()->user()?->isAdmin() && $post->user_id) {
                return; // Keep manually set user_id
            }
        });
    }
}

// Admin creating post as another user
$post = Post::create([
    'title' => 'Guest Post',
    'user_id' => 123, // Different user
]);
```

### System-Generated Content

```php
class Notification extends Model
{
    use InteractsWithUser;

    public static function createSystem(string $message)
    {
        return static::create([
            'message' => $message,
            'user_id' => null, // Explicitly null for system notifications
            'is_system' => true,
        ]);
    }
}
```

### Scopes for User Content

```php
class Post extends Model
{
    use InteractsWithUser;

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByCurrentUser($query)
    {
        return $query->where('user_id', auth()->id());
    }
}

// Usage
$myPosts = Post::byCurrentUser()->get();
$userPosts = Post::byUser(5)->get();
```

## Working with Relationships

Define the relationship to the User model:

```php
class Post extends Model
{
    use InteractsWithUser;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Or with custom column name
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

// Eager load user
$posts = Post::with('user')->get();

// Access user
echo $post->user->name;
```

## Methods Reference

### Instance Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `getUserIdColumnName()` | `string` | Get the user ID column name |

## Combining with Other Traits

```php
class Post extends Model
{
    use InteractsWithUser;
    use InteractsWithUuid;
    use InteractsWithSlug;

    protected $fillable = ['title', 'content'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

$post = Post::create(['title' => 'My Post']);
// uuid: auto-generated
// slug: auto-generated from title
// user_id: auto-assigned from auth
```

## Testing

Example test cases:

```php
use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InteractsWithUserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_assigns_authenticated_user_id()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = Post::factory()->create();

        $this->assertEquals($user->id, $post->user_id);
    }

    /** @test */
    public function it_does_not_assign_when_not_authenticated()
    {
        $post = Post::factory()->create();

        $this->assertNull($post->user_id);
    }

    /** @test */
    public function it_respects_manually_set_user_id()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $otherUser = User::factory()->create();

        $post = Post::factory()->create(['user_id' => $otherUser->id]);

        $this->assertEquals($otherUser->id, $post->user_id);
    }
}
```

## Best Practices

### 1. Always Define the Relationship

```php
// Good
class Post extends Model
{
    use InteractsWithUser;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

// Can easily access: $post->user
```

### 2. Use Foreign Key Constraints

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')
          ->constrained()
          ->cascadeOnDelete(); // or ->nullOnDelete()
    $table->timestamps();
});
```

### 3. Consider Soft Deletes for Users

```php
// On users table
$table->softDeletes();

// Keeps user_id intact even when user is soft deleted
```

### 4. Validate User Ownership

```php
class PostController extends Controller
{
    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $post->update($request->validated());
    }
}

// PostPolicy
public function update(User $user, Post $post)
{
    return $user->id === $post->user_id;
}
```

## Troubleshooting

### User ID Not Set

**Problem**: user_id is null even when authenticated.

**Solutions**:
1. Check column exists: `user_id`
2. Verify user is authenticated: `Auth::check()`
3. Check column not in `$guarded` array
4. Ensure `Auth::id()` returns a value

### Wrong User ID Assigned

**Problem**: Incorrect user_id is being set.

**Check**:
1. Verify authentication is correct: `Auth::id()`
2. Check for middleware changing user context
3. Ensure no manual assignment is overriding

### Foreign Key Constraint Errors

**Problem**: Database rejects save due to foreign key.

**Solutions**:
1. Ensure user exists before creating records
2. Use nullable foreign key if optional:
```php
$table->foreignId('user_id')->nullable()->constrained();
```

## Security Considerations

### Mass Assignment Protection

```php
class Post extends Model
{
    use InteractsWithUser;

    protected $fillable = ['title', 'content'];
    // user_id is NOT in fillable - automatically set
}
```

### Authorization Checks

```php
// Always verify ownership
public function update(Request $request, Post $post)
{
    if ($post->user_id !== auth()->id()) {
        abort(403);
    }

    $post->update($request->validated());
}
```

## Next Steps

- [InteractsWithMeta](06-interacts-with-meta.md) - Manage metadata
- [InteractsWithTags](02-interacts-with-tags.md) - Tag management
- [Getting Started](../01-getting-started/03-basic-usage.md) - Basic trait usage
