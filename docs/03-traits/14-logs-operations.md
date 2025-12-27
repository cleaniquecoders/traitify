# LogsOperations

Unified logging trait for consistent operation logging across Actions, Services,
and Livewire Forms.

## Overview

The `LogsOperations` trait provides structured logging with context enrichment,
sensitive data sanitization, and standardized log formats. It combines
functionality for form operations, service operations, and API interactions
into a single cohesive trait.

## Installation

The trait is available in the `CleaniqueCoders\Traitify\Concerns` namespace.

```php
use CleaniqueCoders\Traitify\Concerns\LogsOperations;
use CleaniqueCoders\Traitify\Contracts\HasLogging;

class MyService implements HasLogging
{
    use LogsOperations;
}
```

## Contract

The `HasLogging` contract defines the required logging interface:

```php
namespace CleaniqueCoders\Traitify\Contracts;

use Throwable;

interface HasLogging
{
    public function getLogContext(): string;
    public function logDebug(string $message, array $context = []): void;
    public function logInfo(string $message, array $context = []): void;
    public function logWarning(string $message, array $context = []): void;
    public function logError(string $message, array $context = []): void;
    public function logException(
        Throwable $e,
        string $operation,
        array $context = []
    ): void;
}
```

## Basic Logging Methods

### Core Log Methods

```php
class OrderService implements HasLogging
{
    use LogsOperations;

    public function processOrder(array $data)
    {
        // Debug level - development information
        $this->logDebug('Processing order', ['order_id' => $data['id']]);

        // Info level - normal operations
        $this->logInfo('Order validated successfully');

        // Warning level - potential issues
        $this->logWarning('Low stock detected', ['product_id' => 123]);

        // Error level - failures
        $this->logError('Payment failed', ['reason' => 'insufficient_funds']);
    }
}
```

### Exception Logging

Log exceptions with full stack trace:

```php
public function createOrder(array $data)
{
    try {
        // Process order...
    } catch (Throwable $e) {
        $this->logException($e, 'Order creation', [
            'order_data' => $data,
        ]);
        throw $e;
    }
}
```

Output includes:

- Exception class name
- Error message
- File and line number
- Full stack trace

## Operation Lifecycle Logging

Track operation start, success, and failure:

```php
class ImportService implements HasLogging
{
    use LogsOperations;

    public function importUsers(array $users)
    {
        $this->logOperationStart('User import', ['count' => count($users)]);

        try {
            foreach ($users as $user) {
                // Import logic...
            }
            $this->logOperationSuccess('User import', ['imported' => count($users)]);
        } catch (Throwable $e) {
            $this->logOperationFailure('User import', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
```

## Method Lifecycle Logging

Track method entry, success, and failure:

```php
class PaymentGateway implements HasLogging
{
    use LogsOperations;

    public function charge(string $customerId, float $amount)
    {
        $this->logMethodEntry(__METHOD__, [
            'customer_id' => $customerId,
            'amount' => $amount,
        ]);

        try {
            $result = $this->processCharge($customerId, $amount);
            $this->logMethodSuccess(__METHOD__, ['transaction_id' => $result->id]);

            return $result;
        } catch (Throwable $e) {
            $this->logMethodFailure(__METHOD__, ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
```

## Form-Specific Logging

### Validation Logging

```php
class UserForm implements HasLogging
{
    use LogsOperations;

    public function validate(array $data, array $rules)
    {
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            $this->logValidation($data, $validator->errors()->toArray());

            return false;
        }

        $this->logValidation($data); // null errors = passed

        return true;
    }
}
```

### State Change Logging

```php
class ProfileForm implements HasLogging
{
    use LogsOperations;

    public function updateEmail(string $oldEmail, string $newEmail)
    {
        $this->logStateChange('email', $oldEmail, $newEmail);
        // Update logic...
    }
}
```

## API Logging

### Log API Calls

```php
class ExternalApiService implements HasLogging
{
    use LogsOperations;

    public function fetchData(string $endpoint)
    {
        $this->logApiCall('GET', $endpoint, ['timeout' => 30]);

        $response = Http::get($endpoint);

        $this->logApiResponse($endpoint, $response->status(), [
            'body_size' => strlen($response->body()),
        ]);

        return $response->json();
    }
}
```

### Log Cache Operations

```php
class CacheService implements HasLogging
{
    use LogsOperations;

    public function get(string $key)
    {
        if (Cache::has($key)) {
            $this->logCacheOperation('get', $key, hit: true);

            return Cache::get($key);
        }

        $this->logCacheOperation('get', $key, hit: false);

        return null;
    }
}
```

## Context Helpers

### Database Context

```php
class UserRepository implements HasLogging
{
    use LogsOperations;

    public function delete(int $userId)
    {
        $context = $this->dbContext('DELETE', 'users', $userId);
        $this->logInfo('Deleting user', $context);

        User::destroy($userId);
    }
}
```

### External Service Context

```php
class NotificationService implements HasLogging
{
    use LogsOperations;

    public function sendSms(string $phone, string $message)
    {
        $context = $this->externalServiceContext('Twilio', 'send_sms');
        $this->logInfo('Sending SMS', array_merge($context, [
            'phone' => $phone,
        ]));
    }
}
```

## Automatic Context Enrichment

All log messages are automatically enriched with:

| Context Key  | Description                         |
|--------------|-------------------------------------|
| `class`      | Full class name                     |
| `user_id`    | Current authenticated user ID       |
| `uuid`       | UUID property if available          |
| `state_keys` | State array keys for Livewire forms |

```php
// Output example
[OrderService] Order created
{
    "class": "App\\Services\\OrderService",
    "user_id": 42,
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "order_id": 123
}
```

## Sensitive Data Sanitization

The trait automatically redacts sensitive data:

```php
$this->logDebug('User data', [
    'email' => 'user@example.com',    // Logged as-is
    'password' => 'secret123',         // Logged as ***REDACTED***
    'api_key' => 'sk_live_xxx',        // Logged as ***REDACTED***
    'token' => 'bearer_xxx',           // Logged as ***REDACTED***
]);
```

### Redacted Keys

- `password`
- `password_confirmation`
- `token`
- `secret`
- `api_key`
- `key`
- `apikey`
- `authorization`
- `bearer`
- `credentials`

Nested arrays are also sanitized recursively.

## Data Summarization

Large data is summarized to prevent log bloat:

```php
$result = [
    'users' => $usersCollection,      // Logged as "array(150 items)"
    'settings' => $settingsObject,    // Logged as "object(Settings)"
    'description' => $longText,       // Truncated to 100 chars
];

$this->logMethodSuccess(__METHOD__, $result);
```

## Custom Log Context

Override `getLogContext()` to customize the log prefix:

```php
class OrderService implements HasLogging
{
    use LogsOperations;

    public function getLogContext(): string
    {
        return 'Orders';
    }
}

// Output: [Orders] Order created
```

## Complete Example

```php
<?php

namespace App\Services;

use CleaniqueCoders\Traitify\Concerns\LogsOperations;
use CleaniqueCoders\Traitify\Contracts\HasLogging;
use Illuminate\Support\Facades\Http;
use Throwable;

class PaymentService implements HasLogging
{
    use LogsOperations;

    public function processPayment(array $paymentData): array
    {
        $this->logOperationStart('Payment processing', [
            'amount' => $paymentData['amount'],
            'currency' => $paymentData['currency'],
        ]);

        try {
            // Validate payment data
            if (! $this->validatePayment($paymentData)) {
                $this->logOperationFailure('Payment processing', [
                    'reason' => 'validation_failed',
                ]);

                return ['success' => false, 'error' => 'Invalid payment data'];
            }

            // Call payment gateway
            $this->logApiCall('POST', '/api/charges', [
                'amount' => $paymentData['amount'],
            ]);

            $response = Http::post('/api/charges', $paymentData);

            $this->logApiResponse('/api/charges', $response->status());

            if ($response->successful()) {
                $this->logOperationSuccess('Payment processing', [
                    'transaction_id' => $response->json('id'),
                ]);

                return ['success' => true, 'data' => $response->json()];
            }

            $this->logOperationFailure('Payment processing', [
                'status' => $response->status(),
            ]);

            return ['success' => false, 'error' => 'Payment failed'];
        } catch (Throwable $e) {
            $this->logException($e, 'Payment processing', $paymentData);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function validatePayment(array $data): bool
    {
        $errors = null;

        if (empty($data['amount']) || $data['amount'] <= 0) {
            $errors = ['amount' => 'Invalid amount'];
        }

        $this->logValidation($data, $errors);

        return $errors === null;
    }
}
```

## Method Reference

| Method                   | Description                    |
|--------------------------|--------------------------------|
| `logDebug()`             | Debug level logging            |
| `logInfo()`              | Info level logging             |
| `logWarning()`           | Warning level logging          |
| `logError()`             | Error level logging            |
| `logException()`         | Exception with stack trace     |
| `logOperationStart()`    | Mark operation start           |
| `logOperationSuccess()`  | Mark operation success         |
| `logOperationFailure()`  | Mark operation failure         |
| `logMethodEntry()`       | Log method entry with params   |
| `logMethodSuccess()`     | Log method success with result |
| `logMethodFailure()`     | Log method failure             |
| `logValidation()`        | Log validation result          |
| `logStateChange()`       | Log state/field change         |
| `logApiCall()`           | Log outgoing API request       |
| `logApiResponse()`       | Log API response               |
| `logCacheOperation()`    | Log cache hit/miss             |
| `dbContext()`            | Create database context        |
| `externalServiceContext` | Create external service context|
| `sanitize()`             | Sanitize sensitive data        |
| `summarize()`            | Summarize large data           |

## Related Documentation

- [InteractsWithApi](10-interacts-with-api.md) - API response formatting
- [Generators](../04-generators/README.md) - Generator system
- [Examples](../06-examples/README.md) - Real-world patterns
