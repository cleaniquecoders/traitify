# Generator Pattern

Deep dive into the Generator pattern used by Traitify for value generation.

## The ValueGenerator Interface

All generators implement the ValueGenerator contract:

```php
interface ValueGenerator
{
    public function generate(array $context = []): mixed;
    public function validate(mixed $value, array $context = []): bool;
    public function getConfig(): array;
    public function setConfig(array $config): self;
}
```

### Method Responsibilities

#### `generate(array $context = []): mixed`

Generates a new value based on the generator's configuration and optional context.

**Context Parameters**:

- `model` - The Eloquent model instance
- `column` - The column name being populated
- `source` - Source data for generation (e.g., slug source text)

**Example**:

```php
$generator = new TokenGenerator(['length' => 32]);
$token = $generator->generate([
    'model' => $apiKey,
    'column' => 'token',
]);
```

#### `validate(mixed $value, array $context = []): bool`

Validates whether a value matches the generator's expected format.

**Example**:

```php
$generator = new TokenGenerator(['length' => 32]);
$generator->validate('abc123'); // false (wrong length)
$generator->validate(str_repeat('x', 32)); // true
```

#### `getConfig(): array`

Returns the current generator configuration.

```php
$config = $generator->getConfig();
// ['length' => 32, 'pool' => 'auto', ...]
```

#### `setConfig(array $config): self`

Merges new configuration with existing config.

```php
$generator->setConfig(['prefix' => 'API_']);
```

## AbstractValueGenerator Base Class

The abstract base class provides common functionality:

```php
abstract class AbstractValueGenerator implements ValueGenerator
{
    protected array $config = [];

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
    }

    abstract protected function getDefaultConfig(): array;

    protected function getConfigValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }
}
```

### Features Provided

1. **Configuration Management**
   - Merges default + custom config
   - Dot notation access via `getConfigValue()`
   - Config getter/setter methods

2. **Default Configuration**
   - Each generator defines its defaults
   - User config overrides defaults

3. **Helper Methods**
   - `getConfigValue()` - Safe config access
   - Supports nested config via dot notation

## Creating a Generator

### Step 1: Extend AbstractValueGenerator

```php
use CleaniqueCoders\Traitify\Generators\AbstractValueGenerator;

class MyGenerator extends AbstractValueGenerator
{
    // Step 2: Define default configuration
    protected function getDefaultConfig(): array
    {
        return [
            'option1' => 'default',
            'option2' => 100,
        ];
    }

    // Step 3: Implement generate()
    public function generate(array $context = []): mixed
    {
        $option1 = $this->getConfigValue('option1');
        $option2 = $this->getConfigValue('option2');

        // Your generation logic
        return "generated-value-{$option1}-{$option2}";
    }

    // Step 4: Implement validate()
    public function validate(mixed $value, array $context = []): bool
    {
        // Your validation logic
        return is_string($value) && str_starts_with($value, 'generated-value-');
    }
}
```

### Step 2: Use Your Generator

#### Option 1: Model Property

```php
class MyModel extends Model
{
    use InteractsWithToken;

    protected $tokenGenerator = MyGenerator::class;
    protected $tokenGeneratorConfig = [
        'option1' => 'custom',
        'option2' => 200,
    ];
}
```

#### Option 2: Configuration File

```php
// config/traitify.php
'generators' => [
    'token' => [
        'class' => MyGenerator::class,
        'config' => [
            'option1' => 'custom',
            'option2' => 200,
        ],
    ],
],
```

## Built-in Generators

### TokenGenerator

Generates random tokens with various character pools.

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

**Use Cases**:

- API keys
- Session tokens
- Reset tokens
- Verification codes

### UuidGenerator

Generates UUIDs in multiple versions and formats.

**Configuration**:

```php
[
    'version' => 'ordered', // ordered, v1, v3, v4, v5
    'format' => 'string',   // string, binary, hex
    'prefix' => '',
    'suffix' => '',
    'namespace' => null,    // For v3/v5
    'name' => null,         // For v3/v5
]
```

**Use Cases**:

- Primary keys
- External IDs
- Distributed systems
- API resources

### SlugGenerator

Generates URL-friendly slugs from text.

**Configuration**:

```php
[
    'separator' => '-',
    'language' => 'en',
    'dictionary' => ['@' => 'at'],
    'lowercase' => true,
    'prefix' => '',
    'suffix' => '',
    'max_length' => null,
    'unique' => false,
]
```

**Use Cases**:

- Blog post URLs
- Product permalinks
- Category paths
- User profiles

## Generator Context

The `context` parameter provides additional information to generators:

### Common Context Keys

| Key | Description | Used By |
|-----|-------------|---------|
| `model` | The Eloquent model instance | All generators |
| `column` | Column name being populated | All generators |
| `source` | Source text for generation | SlugGenerator |

### Using Context

```php
public function generate(array $context = []): string
{
    $model = $context['model'] ?? null;
    $column = $context['column'] ?? 'default';

    // Use model for uniqueness checks
    if ($this->getConfigValue('unique') && $model) {
        return $this->ensureUnique($value, $model, $column);
    }

    return $value;
}
```

## Configuration Merging

Configuration is merged in this order (last wins):

```
1. Generator's getDefaultConfig()
2. Config file (config/traitify.php)
3. Model property ($tokenGeneratorConfig)
4. Constructor parameter
```

**Example**:

```php
// 1. Default config in generator
class TokenGenerator extends AbstractValueGenerator
{
    protected function getDefaultConfig(): array
    {
        return ['length' => 128]; // Default
    }
}

// 2. App config overrides default
// config/traitify.php
'generators' => [
    'token' => [
        'class' => TokenGenerator::class,
        'config' => ['length' => 64], // Overrides 128
    ],
],

// 3. Model config overrides app config
class ApiKey extends Model
{
    protected $tokenGeneratorConfig = [
        'length' => 32 // Overrides 64
    ];
}

// 4. Runtime config overrides all
$generator = new TokenGenerator(['length' => 16]); // Overrides 32
```

## Best Practices

### 1. Validate Configuration

```php
public function __construct(array $config = [])
{
    parent::__construct($config);

    $length = $this->getConfigValue('length');
    if ($length < 1 || $length > 1000) {
        throw new \InvalidArgumentException('Length must be between 1 and 1000');
    }
}
```

### 2. Use Descriptive Config Keys

```php
// Good
'separator' => '-'
'max_length' => 100
'unique' => true

// Bad
'sep' => '-'
'maxLen' => 100
'uniq' => true
```

### 3. Provide Sensible Defaults

```php
protected function getDefaultConfig(): array
{
    return [
        'length' => 128,          // Secure default
        'pool' => 'auto',         // Safe default
        'uppercase' => false,     // User-friendly default
    ];
}
```

### 4. Document Configuration Options

```php
/**
 * TokenGenerator Configuration:
 *
 * - length (int): Token length (default: 128)
 * - pool (string): Character pool - auto|alpha|alphanumeric|numeric|hex
 * - prefix (string): Prefix to add to token
 * - suffix (string): Suffix to add to token
 * - uppercase (bool): Convert to uppercase
 */
class TokenGenerator extends AbstractValueGenerator
```

## Next Steps

- [Resolution Strategy](03-resolution-strategy.md) - How generators are selected
- [Custom Generators](../07-advanced/README.md) - Build your own
- [Configuration](../05-configuration/README.md) - Configure generators
