# InteractsWithEnum Trait

A Laravel trait that extends PHP 8.1+ enum functionality with convenient helper methods for generating values, labels, and options.

## Overview

The InteractsWithEnum trait adds useful methods to PHP enums, making them easier to use in Laravel applications for things like form selects, API responses, and validation.

## Requirements

- PHP 8.1 or higher
- Laravel 9.0 or higher

## Installation

This trait is part of the `cleaniquecoders/traitify` package.

```bash
composer require cleaniquecoders/traitify
```

## Basic Usage

### 1. Create an Enum with the Trait

```php
<?php

namespace App\Enums;

use CleaniqueCoders\Traitify\Concerns\InteractsWithEnum;

enum Status: string
{
    use InteractsWithEnum;

    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::PUBLISHED => 'Published',
            self::ARCHIVED => 'Archived',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::DRAFT => 'Post is in draft state',
            self::PUBLISHED => 'Post is published and visible',
            self::ARCHIVED => 'Post is archived',
        };
    }
}
```

### 2. Use the Helper Methods

```php
// Get all values
$values = Status::values();
// ['draft', 'published', 'archived']

// Get all labels
$labels = Status::labels();
// ['Draft', 'Published', 'Archived']

// Get options (for selects)
$options = Status::options();
// [
//     ['value' => 'draft', 'label' => 'Draft', 'description' => 'Post is in draft state'],
//     ['value' => 'published', 'label' => 'Published', 'description' => 'Post is published and visible'],
//     ['value' => 'archived', 'label' => 'Archived', 'description' => 'Post is archived'],
// ]
```

## Features

### Get All Values

Retrieve array of all enum values:

```php
Status::values();
// ['draft', 'published', 'archived']
```

### Get All Labels

Retrieve array of all enum labels:

```php
Status::labels();
// ['Draft', 'Published', 'Archived']
```

### Get Complete Options

Get array with value, label, and description for each case:

```php
Status::options();
```

## Examples

### Post Status Enum

```php
enum PostStatus: string
{
    use InteractsWithEnum;

    case DRAFT = 'draft';
    case REVIEW = 'review';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::REVIEW => 'Under Review',
            self::PUBLISHED => 'Published',
            self::ARCHIVED => 'Archived',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::DRAFT => 'Post is being written',
            self::REVIEW => 'Post is waiting for review',
            self::PUBLISHED => 'Post is live on the site',
            self::ARCHIVED => 'Post is no longer active',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'gray',
            self::REVIEW => 'yellow',
            self::PUBLISHED => 'green',
            self::ARCHIVED => 'red',
        };
    }
}
```

### User Role Enum

```php
enum UserRole: string
{
    use InteractsWithEnum;

    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case AUTHOR = 'author';
    case SUBSCRIBER = 'subscriber';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function description(): string
    {
        return match($this) {
            self::ADMIN => 'Full access to all features',
            self::EDITOR => 'Can edit and publish posts',
            self::AUTHOR => 'Can write and submit posts',
            self::SUBSCRIBER => 'Can read and comment',
        };
    }

    public function permissions(): array
    {
        return match($this) {
            self::ADMIN => ['*'],
            self::EDITOR => ['posts.*', 'comments.*'],
            self::AUTHOR => ['posts.create', 'posts.update'],
            self::SUBSCRIBER => ['posts.read', 'comments.create'],
        };
    }
}
```

### Order Status Enum

```php
enum OrderStatus: string
{
    use InteractsWithEnum;

    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending Payment',
            self::PROCESSING => 'Processing',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::PENDING => 'Waiting for payment confirmation',
            self::PROCESSING => 'Order is being prepared',
            self::SHIPPED => 'Order has been shipped',
            self::DELIVERED => 'Order has been delivered',
            self::CANCELLED => 'Order was cancelled',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match($this) {
            self::PENDING => in_array($newStatus, [self::PROCESSING, self::CANCELLED]),
            self::PROCESSING => in_array($newStatus, [self::SHIPPED, self::CANCELLED]),
            self::SHIPPED => $newStatus === self::DELIVERED,
            default => false,
        };
    }
}
```

## Usage in Laravel

### In Blade Views (Select)

```blade
<select name="status">
    @foreach(\App\Enums\Status::options() as $option)
        <option value="{{ $option['value'] }}"
                title="{{ $option['description'] }}">
            {{ $option['label'] }}
        </option>
    @endforeach
</select>
```

### In Form Requests (Validation)

```php
use App\Enums\Status;

class UpdatePostRequest extends FormRequest
{
    public function rules()
    {
        return [
            'status' => ['required', 'in:' . implode(',', Status::values())],
        ];
    }
}
```

### In Models

```php
use App\Enums\PostStatus;

class Post extends Model
{
    protected $casts = [
        'status' => PostStatus::class,
    ];
}

$post = Post::find(1);
echo $post->status->label(); // "Published"
echo $post->status->description(); // "Post is live on the site"
```

### In API Resources

```php
use App\Enums\Status;

class PostResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'description' => $this->status->description(),
            ],
        ];
    }
}
```

### In Controllers

```php
use App\Enums\Status;

class PostController extends Controller
{
    public function create()
    {
        return view('posts.create', [
            'statuses' => Status::options(),
        ]);
    }
}
```

## Methods Reference

### Static Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `values()` | `array` | Array of all enum values |
| `labels()` | `array` | Array of all enum labels |
| `options()` | `array` | Array of options with value, label, description |

### Required Instance Methods

Your enum must implement these methods:

| Method | Returns | Description |
|--------|---------|-------------|
| `label()` | `string` | Human-readable label for the enum case |
| `description()` | `string` | Detailed description of the enum case |

## Best Practices

### 1. Always Implement label() and description()

```php
// Required
public function label(): string
{
    return match($this) {
        self::CASE1 => 'Label 1',
        self::CASE2 => 'Label 2',
    };
}

public function description(): string
{
    return match($this) {
        self::CASE1 => 'Description 1',
        self::CASE2 => 'Description 2',
    };
}
```

### 2. Use Backed Enums

```php
// Good - backed enum
enum Status: string
{
    case ACTIVE = 'active';
}

// Avoid - pure enum (harder to work with databases)
enum Status
{
    case ACTIVE;
}
```

### 3. Keep Values Database-Friendly

```php
// Good
enum Status: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}

// Avoid
enum Status: string
{
    case ACTIVE = 'This is Active!';
}
```

### 4. Add Color/Icon Methods for UI

```php
public function color(): string
{
    return match($this) {
        self::ACTIVE => 'green',
        self::INACTIVE => 'gray',
    };
}

public function icon(): string
{
    return match($this) {
        self::ACTIVE => 'check-circle',
        self::INACTIVE => 'x-circle',
    };
}
```

## Testing

Example test cases:

```php
use Tests\TestCase;
use App\Enums\Status;

class InteractsWithEnumTest extends TestCase
{
    /** @test */
    public function it_returns_all_values()
    {
        $values = Status::values();

        $this->assertIsArray($values);
        $this->assertContains('draft', $values);
        $this->assertContains('published', $values);
    }

    /** @test */
    public function it_returns_all_labels()
    {
        $labels = Status::labels();

        $this->assertIsArray($labels);
        $this->assertContains('Draft', $labels);
        $this->assertContains('Published', $labels);
    }

    /** @test */
    public function it_returns_options_with_structure()
    {
        $options = Status::options();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('value', $options[0]);
        $this->assertArrayHasKey('label', $options[0]);
        $this->assertArrayHasKey('description', $options[0]);
    }
}
```

## Next Steps

- [Getting Started](../01-getting-started/README.md) - Basic usage
- [Examples](../06-examples/README.md) - Real-world examples
