# Resolution Strategy

Understanding how Traitify resolves which generator to use.

## The Three-Tier System

Traitify uses a cascading resolution strategy with three tiers:

```
┌─────────────────────────────────────┐
│  1. Model Property                  │  Highest Priority
│     $tokenGenerator                 │
│     $tokenGeneratorConfig           │
├─────────────────────────────────────┤
│  2. Configuration File              │  Medium Priority
│     config('traitify.generators')   │
├─────────────────────────────────────┤
│  3. Default Generator               │  Lowest Priority
│     TokenGenerator::class           │
└─────────────────────────────────────┘
```

## Resolution Process

### Step 1: Check Model Property

The resolver first checks if the model defines a custom generator:

```php
class ApiKey extends Model
{
    use InteractsWithToken;

    // This takes highest priority
    protected $tokenGenerator = CustomGenerator::class;

    // Optional: Configure the generator
    protected $tokenGeneratorConfig = [
        'length' => 32,
        'prefix' => 'sk_',
    ];
}
```

**When this is used**:

- Model-specific customization needed
- Different models need different generators
- Overriding app-wide defaults for specific cases

### Step 2: Check Configuration File

If no model property is found, check the config file:

```php
// config/traitify.php
'generators' => [
    'token' => [
        'class' => \App\Generators\CompanyTokenGenerator::class,
        'config' => [
            'length' => 64,
            'pool' => 'hex',
        ],
    ],
],
```

**When this is used**:

- App-wide default generator
- Consistent behavior across all models
- Environment-specific generation

### Step 3: Fallback to Default

If neither model property nor config is found, use the default:

```php
// Hardcoded defaults in HasGeneratorResolver
protected function getDefaultGeneratorClass(string $type): string
{
    return match ($type) {
        'token' => TokenGenerator::class,
        'uuid' => UuidGenerator::class,
        'slug' => SlugGenerator::class,
    };
}
```

**When this is used**:

- Fresh installation (no config published)
- No customization needed
- Sensible defaults work for your use case

## The HasGeneratorResolver Trait

The core resolution logic lives in `HasGeneratorResolver`:

```php
trait HasGeneratorResolver
{
    protected function resolveGenerator(
        string $type,
        ?string $propertyName = null,
        ?string $configPropertyName = null
    ): ValueGenerator {
        // 1. Check model property
        if ($propertyName && isset($this->{$propertyName})) {
            $generatorClass = $this->{$propertyName};

            if (is_string($generatorClass)) {
                return $this->instantiateGenerator($generatorClass, $configPropertyName);
            }

            if ($generatorClass instanceof ValueGenerator) {
                return $generatorClass;
            }
        }

        // 2. Check config
        $configKey = "traitify.generators.{$type}";
        $generatorConfig = config($configKey);

        if ($generatorConfig && isset($generatorConfig['class'])) {
            $class = $generatorConfig['class'];
            $config = $generatorConfig['config'] ?? [];

            return new $class($config);
        }

        // 3. Fallback to default
        $defaultClass = $this->getDefaultGeneratorClass($type);

        return new $defaultClass();
    }
}
```

## Configuration Merging

When instantiating generators, configuration is merged from multiple sources:

### Generator-Level Merge

```php
class TokenGenerator extends AbstractValueGenerator
{
    public function __construct(array $config = [])
    {
        // Merge: defaults + provided config
        $this->config = array_merge(
            $this->getDefaultConfig(),  // Generator defaults
            $config                      // Provided config
        );
    }
}
```

### Resolution-Level Merge

```php
// Model property + config property
protected function instantiateGenerator(
    string $class,
    ?string $configPropertyName = null
): ValueGenerator {
    $config = [];

    // Get config from model property if set
    if ($configPropertyName && isset($this->{$configPropertyName})) {
        $config = $this->{$configPropertyName};
    }

    return new $class($config);
}
```

## Examples

### Example 1: Using All Three Tiers

```php
// 1. Define app-wide default
// config/traitify.php
'generators' => [
    'token' => [
        'class' => TokenGenerator::class,
        'config' => ['length' => 64], // App default: 64 chars
    ],
],

// 2. Most models use app default
class User extends Model
{
    use InteractsWithToken;
    // Uses config: 64-char tokens
}

// 3. Override for specific model
class ApiKey extends Model
{
    use InteractsWithToken;

    protected $tokenGeneratorConfig = [
        'length' => 128, // API keys need longer tokens
        'prefix' => 'sk_',
    ];
    // Overrides config: 128-char tokens with prefix
}

// 4. Complete custom generator
class PaymentToken extends Model
{
    use InteractsWithToken;

    protected $tokenGenerator = SecurePaymentGenerator::class;
    // Uses completely different generator
}
```

### Example 2: Environment-Specific

```php
// config/traitify.php
'generators' => [
    'token' => [
        'class' => TokenGenerator::class,
        'config' => [
            'length' => env('APP_ENV') === 'testing' ? 16 : 128,
        ],
    ],
],
```

### Example 3: Generator Instance

You can even provide a generator instance:

```php
class ApiKey extends Model
{
    use InteractsWithToken;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Set generator instance at runtime
        $this->tokenGenerator = new TokenGenerator([
            'length' => $this->getTokenLength(),
            'prefix' => $this->getTokenPrefix(),
        ]);
    }

    protected function getTokenLength(): int
    {
        return $this->is_production ? 128 : 32;
    }
}
```

## Resolution Flow Chart

```
Start: Model needs a token
        │
        ├──> Has $tokenGenerator property?
        │           │
        │          YES──> Use that generator ──> END
        │           │
        │           NO
        │           │
        ├──> Has config('traitify.generators.token')?
        │           │
        │          YES──> Use configured generator ──> END
        │           │
        │           NO
        │           │
        └──> Use default TokenGenerator::class ──> END
```

## Best Practices

### 1. Start with Defaults

Don't publish config unless you need customization:

```php
// Just use the trait - defaults work great!
class Post extends Model
{
    use InteractsWithUuid;
}
```

### 2. Use Config for App-Wide Changes

Publish and modify config for app-wide customization:

```bash
php artisan vendor:publish --tag=traitify-config
```

```php
// config/traitify.php
'generators' => [
    'uuid' => [
        'class' => UuidGenerator::class,
        'config' => ['version' => 'v4'], // Change default version
    ],
],
```

### 3. Use Model Properties for Exceptions

Override specific models when needed:

```php
// Most users get default UUIDs
class User extends Model
{
    use InteractsWithUuid;
}

// But admins get a different format
class Admin extends Model
{
    use InteractsWithUuid;

    protected $uuidGeneratorConfig = [
        'prefix' => 'ADMIN_',
        'version' => 'v4',
    ];
}
```

### 4. Document Your Choices

When using custom generators, document why:

```php
class SecurityToken extends Model
{
    use InteractsWithToken;

    /**
     * Security tokens use a custom generator that:
     * - Uses cryptographically secure randomness
     * - Includes timestamp encoding
     * - Has automatic expiry checking
     */
    protected $tokenGenerator = SecurityTokenGenerator::class;
}
```

## Troubleshooting

### Generator Not Being Used

**Problem**: Your generator isn't being used.

**Check**:

1. Model property name matches: `$tokenGenerator` not `$token_generator`
2. Config key matches: `traitify.generators.token`
3. Generator class exists and is imported
4. Config cache cleared: `php artisan config:clear`

### Wrong Configuration Applied

**Problem**: Generator uses wrong config.

**Check resolution order**:

```php
// Add to your model temporarily
public static function bootInteractsWithToken()
{
    parent::bootInteractsWithToken();

    dd([
        'property' => $this->tokenGenerator ?? 'not set',
        'config' => config('traitify.generators.token'),
    ]);
}
```

## Next Steps

- [Generator Pattern](02-generator-pattern.md) - Deep dive into generators
- [Configuration](../05-configuration/README.md) - Config file reference
- [Custom Generators](../07-advanced/README.md) - Build your own
