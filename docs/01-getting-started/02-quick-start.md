# Quick Start Guide

Get up and running with Traitify in minutes.

## Basic UUID Generation

```php
use CleaniqueCoders\Traitify\Concerns\InteractsWithUuid;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use InteractsWithUuid;
}
```

```php
$post = Post::create(['title' => 'My First Post']);
echo $post->uuid; // Auto-generated ordered UUID
```

## Generate Secure Tokens

```php
use CleaniqueCoders\Traitify\Concerns\InteractsWithToken;

class ApiKey extends Model
{
    use InteractsWithToken;
}
```

```php
$apiKey = ApiKey::create(['name' => 'Production Key']);
echo $apiKey->token; // 128-character random token
```

## Create SEO-Friendly Slugs

```php
use CleaniqueCoders\Traitify\Concerns\InteractsWithSlug;

class Article extends Model
{
    use InteractsWithSlug;

    protected $fillable = ['name', 'content'];
}
```

```php
$article = Article::create(['name' => 'Getting Started with Laravel']);
echo $article->slug; // getting-started-with-laravel
```

## Query by Generated Values

```php
// Find by UUID
$post = Post::uuid('9d9e8da7-78c3-4c9d-9f5e-5c8e4a2b1d3c')->first();

// Find by token
$apiKey = ApiKey::token('abc123...')->first();

// Find by slug
$article = Article::slug('getting-started-with-laravel')->first();
```

## Next Steps

- [Basic Usage](03-basic-usage.md) - Learn more about each trait
- [Configuration](../05-configuration/README.md) - Customize behavior
- [Traits Overview](../03-traits/01-overview.md) - Explore all available traits
