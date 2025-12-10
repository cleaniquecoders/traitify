# InteractsWithToken Trait

A Laravel trait that automatically generates secure random tokens for your Eloquent models with extensive configuration options.

## Overview

The InteractsWithToken trait provides automatic token generation with support for various character pools, customizable length, prefixes/suffixes, and flexible configuration at multiple levels.

## Installation

This trait is part of the `cleaniquecoders/traitify` package.

```bash
composer require cleaniquecoders/traitify
```

## Database Setup

Add a token column to your table migration:

```php
Schema::create('api_keys', function (Blueprint $table) {
    $table->id();
    $table->string('token', 128)->unique();
    $table->string('name');
    $table->timestamps();
});
```

## Basic Usage

### 1. Add the Trait to Your Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use CleaniqueCoders\Traitify\Concerns\InteractsWithToken;

class ApiKey extends Model
{
    use InteractsWithToken;

    protected $fillable = ['name'];
}
```

### 2. Tokens are Auto-Generated

```php
$apiKey = ApiKey::create(['name' => 'Production Key']);
echo $apiKey->token; // Auto-generated 128-character token
```

## Features

### Automatic Token Generation

Tokens are automatically generated when creating models:

```php
$key = new ApiKey(['name' => 'My Key']);
$key->save();
// Token is automatically assigned before saving
```

### Query Scope

Find models by token using the query scope:

```php
$apiKey = ApiKey::token('abc123xyz...')->first();
```

### Custom Column Name

Override the default token column name:

```php
class ApiKey extends Model
{
    use InteractsWithToken;

    protected $token_column = 'api_token';
}
```

## Configuration Options

### Token Length

Configure the token length (default: 128):

```php
class ApiKey extends Model
{
    use InteractsWithToken;

    protected $tokenGeneratorConfig = [
        'length' => 64,
    ];
}
```

### Character Pools

Choose from different character pools:

```php
protected $tokenGeneratorConfig = [
    'pool' => 'hex', // auto, alpha, alphanumeric, numeric, hex
];
```

**Available Pools:**

- `auto` (default) - Alphanumeric + special characters
- `alpha` - Letters only (a-z, A-Z)
- `alphanumeric` - Letters and numbers
- `numeric` - Numbers only (0-9)
- `hex` - Hexadecimal (0-9, a-f)

### Prefix and Suffix

Add prefix or suffix to tokens:

```php
protected $tokenGeneratorConfig = [
    'prefix' => 'sk_',
    'suffix' => '_live',
];
// Result: sk_a1b2c3d4..._live
```

### Uppercase Conversion

Convert tokens to uppercase:

```php
protected $tokenGeneratorConfig = [
    'uppercase' => true,
];
// Result: ABC123XYZ...
```

### Complete Configuration Example

```php
class ApiKey extends Model
{
    use InteractsWithToken;

    protected $tokenGeneratorConfig = [
        'length' => 64,
        'pool' => 'hex',
        'prefix' => 'sk_',
        'uppercase' => true,
    ];
}
// Result: SK_A1B2C3D4E5F6... (67 characters total)
```

## App-Wide Configuration

Configure tokens globally in `config/traitify.php`:

```php
'generators' => [
    'token' => [
        'class' => \CleaniqueCoders\Traitify\Generators\TokenGenerator::class,
        'config' => [
            'length' => 128,
            'pool' => 'auto',
        ],
    ],
],
```

## Common Use Cases

### API Keys (Stripe-style)

```php
class ApiKey extends Model
{
    use InteractsWithToken;

    protected $tokenGeneratorConfig = [
        'length' => 64,
        'pool' => 'hex',
        'prefix' => 'sk_',
    ];
}
// Result: sk_a1b2c3d4e5f6...
```

### Short Access Codes

```php
class InviteCode extends Model
{
    use InteractsWithToken;

    protected $token_column = 'code';

    protected $tokenGeneratorConfig = [
        'length' => 8,
        'pool' => 'alphanumeric',
        'uppercase' => true,
    ];
}
// Result: ABC123XY
```

### Verification Codes

```php
class EmailVerification extends Model
{
    use InteractsWithToken;

    protected $token_column = 'verification_code';

    protected $tokenGeneratorConfig = [
        'length' => 6,
        'pool' => 'numeric',
    ];
}
// Result: 123456
```

### Session Tokens

```php
class UserSession extends Model
{
    use InteractsWithToken;

    protected $token_column = 'session_token';

    protected $tokenGeneratorConfig = [
        'length' => 128,
        'pool' => 'auto',
    ];
}
```

### Reset Tokens

```php
class PasswordReset extends Model
{
    use InteractsWithToken;

    protected $token_column = 'reset_token';

    protected $tokenGeneratorConfig = [
        'length' => 64,
        'pool' => 'hex',
    ];
}
```

## Advanced Examples

### Environment-Specific Configuration

```php
class ApiKey extends Model
{
    use InteractsWithToken;

    protected function getTokenGeneratorConfig(): array
    {
        return [
            'length' => app()->environment('production') ? 128 : 32,
            'pool' => 'hex',
        ];
    }
}
```

### Multiple Token Types

```php
class User extends Model
{
    use InteractsWithToken;

    protected $token_column = 'api_token';

    // Separate remember token
    public static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->remember_token = bin2hex(random_bytes(32));
        });
    }
}
```

## Methods Reference

### Instance Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `getTokenColumn()` | `string` | Get the token column name |

### Query Scopes

| Scope | Parameters | Description |
|-------|-----------|-------------|
| `token($value)` | `string` | Find model by token value |

## Security Best Practices

### 1. Use Sufficient Length

```php
// Good for security
protected $tokenGeneratorConfig = [
    'length' => 64, // or higher
];

// Too short, avoid for sensitive operations
protected $tokenGeneratorConfig = [
    'length' => 16, // Easier to brute force
];
```

### 2. Hash Tokens in Database (Optional)

For extra security, store hashed tokens:

```php
class ApiKey extends Model
{
    use InteractsWithToken;

    public static function boot()
    {
        parent::boot();

        static::creating(function ($apiKey) {
            // Store plain token temporarily for response
            $plainToken = $apiKey->token;

            // Hash for storage
            $apiKey->token = hash('sha256', $plainToken);

            // You might want to return plain token once
            $apiKey->plain_token = $plainToken;
        });
    }
}
```

### 3. Rate Limit Token Usage

```php
// In your middleware
public function handle($request, Closure $next)
{
    $token = $request->bearerToken();

    RateLimiter::attempt(
        'api-key:'.$token,
        $perMinute = 60,
        function() use ($next, $request) {
            return $next($request);
        }
    );
}
```

### 4. Implement Token Expiry

```php
Schema::create('api_keys', function (Blueprint $table) {
    $table->id();
    $table->string('token')->unique();
    $table->timestamp('expires_at')->nullable();
    $table->timestamps();
});

class ApiKey extends Model
{
    use InteractsWithToken;

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
```

## Testing

Example test cases:

```php
use Tests\TestCase;
use App\Models\ApiKey;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InteractsWithTokenTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_generates_token_on_creation()
    {
        $apiKey = ApiKey::factory()->create();

        $this->assertNotNull($apiKey->token);
        $this->assertEquals(128, strlen($apiKey->token));
    }

    /** @test */
    public function it_can_find_by_token()
    {
        $apiKey = ApiKey::factory()->create();

        $found = ApiKey::token($apiKey->token)->first();

        $this->assertTrue($found->is($apiKey));
    }

    /** @test */
    public function it_respects_custom_configuration()
    {
        $apiKey = new class extends ApiKey {
            protected $tokenGeneratorConfig = [
                'length' => 32,
                'prefix' => 'test_',
            ];
        };

        $apiKey->name = 'Test';
        $apiKey->save();

        $this->assertStringStartsWith('test_', $apiKey->token);
        $this->assertEquals(37, strlen($apiKey->token)); // 32 + 5 (prefix)
    }
}
```

## Troubleshooting

### Token Not Generated

**Problem**: Token is null after creating model.

**Solutions**:
1. Check column exists in database
2. Ensure column name matches (default: 'token')
3. Verify column is not in `$guarded` array
4. Clear config cache: `php artisan config:clear`

### Token Too Short/Long

**Problem**: Generated token doesn't match expected length.

**Check**: If you're using prefix/suffix, total length will be:
```
total_length = length + prefix_length + suffix_length
```

### Duplicate Token Error

**Problem**: Unique constraint violation (very rare).

**Solution**: This is extremely unlikely with default settings. If it happens:
1. Increase token length
2. Check for manual token assignment conflicts

## Next Steps

- [InteractsWithUuid](03-interacts-with-uuid.md) - Generate UUIDs
- [InteractsWithSlug](05-interacts-with-slug.md) - Create URL-friendly slugs
- [Generators](../04-generators/01-overview.md) - Learn about token generator options
