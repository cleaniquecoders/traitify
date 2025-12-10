# Generators Overview

Traitify's powerful generator system provides flexible, configurable value generation.

## What Are Generators?

Generators are classes that create values for your models. They implement the `ValueGenerator` interface and can be customized through configuration.

```php
interface ValueGenerator
{
    public function generate(array $context = []): mixed;
    public function validate(mixed $value, array $context = []): bool;
    public function getConfig(): array;
    public function setConfig(array $config): self;
}
```

## Built-in Generators

### TokenGenerator

Generates secure random tokens with configurable options.

**[Full Documentation →](token-generator.md)**

**Configuration**:

```php
[
    'length' => 128,
    'pool' => 'auto', // auto, alpha, alphanumeric, numeric, hex
    'prefix' => '',
    'suffix' => '',
    'uppercase' => false,
]
```

**Example**:

```php
$generator = new TokenGenerator(['length' => 64, 'prefix' => 'API_']);
$token = $generator->generate(); // API_a1b2c3d4...
```

### UuidGenerator

Generates UUIDs in multiple versions and formats.

**[Full Documentation →](uuid-generator.md)**

**Configuration**:

```php
[
    'version' => 'ordered', // ordered, v1, v3, v4, v5
    'format' => 'string',   // string, binary, hex
    'prefix' => '',
    'suffix' => '',
]
```

**Example**:

```php
$generator = new UuidGenerator(['version' => 'v4']);
$uuid = $generator->generate(); // 550e8400-e29b-41d4-a716-446655440000
```

### SlugGenerator

Creates URL-friendly slugs from text.

**[Full Documentation →](slug-generator.md)**

**Configuration**:

```php
[
    'separator' => '-',
    'language' => 'en',
    'lowercase' => true,
    'max_length' => null,
    'unique' => false,
]
```

**Example**:

```php
$generator = new SlugGenerator(['unique' => true]);
$slug = $generator->generate(['source' => 'Hello World']); // hello-world
```

## Using Generators

### 1. Default Usage (Zero Config)

```php
class Post extends Model
{
    use InteractsWithUuid;
    // Uses default UuidGenerator with default config
}
```

### 2. App-wide Configuration

```php
// config/traitify.php
'generators' => [
    'uuid' => [
        'class' => UuidGenerator::class,
        'config' => [
            'version' => 'v4',
            'format' => 'string',
        ],
    ],
],
```

### 3. Per-Model Configuration

```php
class Post extends Model
{
    use InteractsWithUuid;

    protected $uuidGeneratorConfig = [
        'version' => 'v4',
        'prefix' => 'POST_',
    ];
}
```

### 4. Custom Generator Class

```php
class Post extends Model
{
    use InteractsWithUuid;

    protected $uuidGenerator = MyCustomUuidGenerator::class;
}
```

## Generator Features

### Configuration Merging

Configuration is merged from multiple sources:

```
Generator Defaults
    ↓
Config File (config/traitify.php)
    ↓
Model Property ($uuidGeneratorConfig)
    ↓
Runtime (constructor parameter)
```

### Context Awareness

Generators receive context about the model and column:

```php
$generator->generate([
    'model' => $post,
    'column' => 'uuid',
    'source' => 'Hello World', // For slugs
]);
```

### Validation

Generators can validate values:

```php
$generator = new TokenGenerator(['length' => 32]);

$generator->validate('abc123'); // false (wrong length)
$generator->validate(str_repeat('x', 32)); // true
```

## Generator Comparison

| Generator | Configurable Length | Character Pools | Uniqueness | Prefixes |
|-----------|-------------------|-----------------|------------|----------|
| TokenGenerator | ✅ | ✅ | ❌ | ✅ |
| UuidGenerator | ❌ | ❌ | ✅ (by design) | ✅ |
| SlugGenerator | ✅ (max) | ❌ | ✅ (optional) | ✅ |

## Common Use Cases

### API Keys

```php
protected $tokenGeneratorConfig = [
    'length' => 64,
    'pool' => 'hex',
    'prefix' => 'sk_',
    'uppercase' => true,
];
// Result: SK_A1B2C3D4E5F6...
```

### Short Tokens

```php
protected $tokenGeneratorConfig = [
    'length' => 6,
    'pool' => 'alphanumeric',
    'uppercase' => true,
];
// Result: ABC123
```

### Verification Codes

```php
protected $tokenGeneratorConfig = [
    'length' => 6,
    'pool' => 'numeric',
];
// Result: 123456
```

### Primary Key UUIDs

```php
protected $uuidGeneratorConfig = [
    'version' => 'ordered', // Better for database indexing
    'format' => 'string',
];
```

### External IDs

```php
protected $uuidGeneratorConfig = [
    'version' => 'v4',
    'prefix' => 'EXT_',
];
// Result: EXT_550e8400-e29b-41d4-a716-446655440000
```

### Blog Post Slugs

```php
protected $slugGeneratorConfig = [
    'unique' => true,
    'max_length' => 100,
];
```

### Product SKUs

```php
protected $slugGeneratorConfig = [
    'separator' => '_',
    'uppercase' => true,
    'prefix' => 'SKU-',
];
// Result: SKU-PRODUCT_NAME
```

## Best Practices

### 1. Use Sensible Defaults

Don't configure unless you need to:

```php
// Good - uses sensible defaults
class Post extends Model
{
    use InteractsWithUuid;
}

// Over-engineering
class Post extends Model
{
    use InteractsWithUuid;

    protected $uuidGeneratorConfig = [
        'version' => 'ordered', // This is already the default
    ];
}
```

### 2. Configure at the Right Level

- **Defaults**: No config needed
- **App-wide**: Use config file
- **Model-specific**: Use model properties

### 3. Document Custom Generators

```php
class ApiKey extends Model
{
    use InteractsWithToken;

    /**
     * API keys use hex tokens with sk_ prefix
     * for Stripe-style compatibility.
     */
    protected $tokenGeneratorConfig = [
        'length' => 64,
        'pool' => 'hex',
        'prefix' => 'sk_',
    ];
}
```

### 4. Test Your Generators

```php
public function test_api_key_format()
{
    $apiKey = ApiKey::factory()->create();

    $this->assertStringStartsWith('sk_', $apiKey->token);
    $this->assertEquals(67, strlen($apiKey->token)); // 64 + 3 (prefix)
}
```

## Next Steps

- [Architecture](../02-architecture/README.md) - Generator pattern and design
- [Configuration](../05-configuration/README.md) - Configuration options
- [Custom Generators](../07-advanced/README.md) - Build your own
