# Basic Usage

Learn the fundamentals of using Traitify traits in your Laravel models.

## How Traits Work

Traitify traits use Laravel's Eloquent model boot lifecycle to automatically generate values when models are created.

```php
use CleaniqueCoders\Traitify\Concerns\InteractsWithToken;

class Model
{
    use InteractsWithToken;

    // The trait hooks into the 'creating' event
    // Automatically generates a token before saving
}
```

## Column Customization

### Default Column Names

Each trait assumes standard column names:

- `InteractsWithUuid` → `uuid`
- `InteractsWithToken` → `token`
- `InteractsWithSlug` → `slug`
- `InteractsWithMeta` → `meta`
- `InteractsWithUser` → `user_id`

### Custom Column Names

Override the default column names using properties:

```php
class Post extends Model
{
    use InteractsWithUuid;

    protected $uuid_column = 'id'; // Use 'id' instead of 'uuid'
}
```

```php
class ApiKey extends Model
{
    use InteractsWithToken;

    protected $token_column = 'api_token'; // Custom column name
}
```

```php
class Article extends Model
{
    use InteractsWithSlug;

    protected $slug_column = 'permalink';
    protected $slug_source_column = 'title'; // Generate from 'title' instead of 'name'
}
```

## Preventing Overwrites

Traits will NOT overwrite existing values:

```php
// Manual token assignment
$apiKey = ApiKey::create(['token' => 'my-custom-token']);
echo $apiKey->token; // 'my-custom-token' (not auto-generated)

// Auto-generated when null
$apiKey = ApiKey::create(['name' => 'Key']);
echo $apiKey->token; // Auto-generated 128-char token
```

## Query Scopes

All generator traits provide query scopes:

```php
// UUID scope
Post::uuid($uuid)->first();

// Token scope
ApiKey::token($token)->first();

// Slug scope
Article::slug($slug)->first();
```

## Database Migrations

Ensure your migrations include the appropriate columns:

### UUID Column

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->string('title');
    $table->timestamps();
});
```

### Token Column

```php
Schema::create('api_keys', function (Blueprint $table) {
    $table->id();
    $table->string('token', 128)->unique();
    $table->string('name');
    $table->timestamps();
});
```

### Slug Column

```php
Schema::create('articles', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('content');
    $table->timestamps();
});
```

## Common Patterns

### UUID as Primary Key

```php
class Post extends Model
{
    use InteractsWithUuid;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $uuid_column = 'id';
}
```

```php
Schema::create('posts', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('title');
    $table->timestamps();
});
```

### Unique Slugs

```php
class Article extends Model
{
    use InteractsWithSlug;

    protected $slugGeneratorConfig = [
        'unique' => true, // Automatically append numbers for uniqueness
    ];
}
```

### Metadata Storage

```php
use InteractsWithMeta;

class Product extends Model
{
    use InteractsWithMeta;

    protected $default_meta = [
        'featured' => false,
        'priority' => 0,
    ];
}
```

```php
$product = Product::create(['name' => 'Widget']);
echo $product->meta; // ['featured' => false, 'priority' => 0]
```

## Next Steps

- [Configuration](../05-configuration/README.md) - Customize generator behavior
- [Generators](../04-generators/01-overview.md) - Advanced generator features
- [Traits Reference](../03-traits/01-overview.md) - Detailed trait documentation
