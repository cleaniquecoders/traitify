<?php

use CleaniqueCoders\Traitify\Concerns\LogsOperations;
use CleaniqueCoders\Traitify\Contracts\HasLogging;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogsOperationsTestService implements HasLogging
{
    use LogsOperations;

    public string $uuid = 'test-uuid-123';

    public array $state = ['name' => 'John', 'email' => 'john@example.com'];
}

class LogsOperationsBasicService implements HasLogging
{
    use LogsOperations;
}

beforeEach(function () {
    Log::spy();
    Auth::shouldReceive('id')->andReturn(42);
});

describe('basic logging methods', function () {
    it('logs debug message with context', function () {
        $service = new LogsOperationsTestService;
        $service->logDebug('Test debug message', ['key' => 'value']);

        Log::shouldHaveReceived('debug')
            ->once()
            ->with(
                '[LogsOperationsTestService] Test debug message',
                Mockery::on(fn ($context) => $context['class'] === LogsOperationsTestService::class
                    && $context['user_id'] === 42
                    && $context['key'] === 'value')
            );
    });

    it('logs info message with context', function () {
        $service = new LogsOperationsTestService;
        $service->logInfo('Test info message');

        Log::shouldHaveReceived('info')
            ->once()
            ->with(
                '[LogsOperationsTestService] Test info message',
                Mockery::on(fn ($context) => $context['class'] === LogsOperationsTestService::class)
            );
    });

    it('logs warning message with context', function () {
        $service = new LogsOperationsTestService;
        $service->logWarning('Test warning message');

        Log::shouldHaveReceived('warning')
            ->once()
            ->with(
                '[LogsOperationsTestService] Test warning message',
                Mockery::type('array')
            );
    });

    it('logs error message with context', function () {
        $service = new LogsOperationsTestService;
        $service->logError('Test error message');

        Log::shouldHaveReceived('error')
            ->once()
            ->with(
                '[LogsOperationsTestService] Test error message',
                Mockery::type('array')
            );
    });
});

describe('exception logging', function () {
    it('logs exception with full details', function () {
        $service = new LogsOperationsTestService;
        $exception = new RuntimeException('Test exception message');

        $service->logException($exception, 'Test operation');

        Log::shouldHaveReceived('error')
            ->once()
            ->with(
                '[LogsOperationsTestService] Test operation failed',
                Mockery::on(fn ($context) => $context['exception'] === RuntimeException::class
                    && $context['message'] === 'Test exception message'
                    && isset($context['file'])
                    && isset($context['line'])
                    && isset($context['trace']))
            );
    });
});

describe('operation lifecycle logging', function () {
    it('logs operation start', function () {
        $service = new LogsOperationsTestService;
        $service->logOperationStart('Data import', ['count' => 100]);

        Log::shouldHaveReceived('info')
            ->once()
            ->with(
                '[LogsOperationsTestService] Data import started',
                Mockery::on(fn ($context) => $context['count'] === 100)
            );
    });

    it('logs operation success', function () {
        $service = new LogsOperationsTestService;
        $service->logOperationSuccess('Data import');

        Log::shouldHaveReceived('info')
            ->once()
            ->with(
                '[LogsOperationsTestService] Data import completed successfully',
                Mockery::type('array')
            );
    });

    it('logs operation failure', function () {
        $service = new LogsOperationsTestService;
        $service->logOperationFailure('Data import', ['reason' => 'timeout']);

        Log::shouldHaveReceived('error')
            ->once()
            ->with(
                '[LogsOperationsTestService] Data import failed',
                Mockery::on(fn ($context) => $context['reason'] === 'timeout')
            );
    });
});

describe('method lifecycle logging', function () {
    it('logs method entry with sanitized params', function () {
        $service = new LogsOperationsTestService;
        $service->logMethodEntry('processOrder', ['order_id' => 123, 'password' => 'secret']);

        Log::shouldHaveReceived('debug')
            ->once()
            ->with(
                '[LogsOperationsTestService] processOrder() called',
                Mockery::on(fn ($context) => $context['params']['order_id'] === 123
                    && $context['params']['password'] === '***REDACTED***')
            );
    });

    it('logs method success with summarized result', function () {
        $service = new LogsOperationsTestService;
        $service->logMethodSuccess('processOrder', [
            'items' => [1, 2, 3, 4, 5],
            'total' => 500,
        ]);

        Log::shouldHaveReceived('info')
            ->once()
            ->with(
                '[LogsOperationsTestService] processOrder() completed successfully',
                Mockery::on(fn ($context) => $context['result_summary']['items'] === 'array(5 items)'
                    && $context['result_summary']['total'] === 500)
            );
    });

    it('logs method failure', function () {
        $service = new LogsOperationsTestService;
        $service->logMethodFailure('processOrder', ['error' => 'Invalid data']);

        Log::shouldHaveReceived('error')
            ->once()
            ->with(
                '[LogsOperationsTestService] processOrder() failed',
                Mockery::on(fn ($context) => $context['error'] === 'Invalid data')
            );
    });
});

describe('validation logging', function () {
    it('logs validation passed', function () {
        $service = new LogsOperationsTestService;
        $service->logValidation(['name' => 'John', 'email' => 'john@example.com']);

        Log::shouldHaveReceived('debug')
            ->once()
            ->with(
                '[LogsOperationsTestService] Validation passed',
                Mockery::on(fn ($context) => $context['data_keys'] === ['name', 'email'])
            );
    });

    it('logs validation failed with errors', function () {
        $service = new LogsOperationsTestService;
        $service->logValidation(
            ['name' => '', 'email' => 'invalid'],
            ['name' => 'Required', 'email' => 'Invalid format']
        );

        Log::shouldHaveReceived('warning')
            ->once()
            ->with(
                '[LogsOperationsTestService] Validation failed',
                Mockery::on(fn ($context) => $context['errors']['name'] === 'Required'
                    && $context['errors']['email'] === 'Invalid format')
            );
    });
});

describe('state change logging', function () {
    it('logs state change with sanitized values', function () {
        $service = new LogsOperationsTestService;
        $service->logStateChange('email', 'old@example.com', 'new@example.com');

        Log::shouldHaveReceived('debug')
            ->once()
            ->with(
                '[LogsOperationsTestService] State changed',
                Mockery::on(fn ($context) => $context['field'] === 'email'
                    && $context['from'] === 'old@example.com'
                    && $context['to'] === 'new@example.com')
            );
    });
});

describe('API logging', function () {
    it('logs API call', function () {
        $service = new LogsOperationsTestService;
        $service->logApiCall('POST', '/api/orders', ['timeout' => 30]);

        Log::shouldHaveReceived('debug')
            ->once()
            ->with(
                '[LogsOperationsTestService] API call: POST /api/orders',
                Mockery::on(fn ($context) => $context['timeout'] === 30)
            );
    });

    it('logs API response with success status as debug', function () {
        $service = new LogsOperationsTestService;
        $service->logApiResponse('/api/orders', 200, ['body_size' => 1024]);

        Log::shouldHaveReceived('debug')
            ->once()
            ->with(
                '[LogsOperationsTestService] API response: /api/orders',
                Mockery::on(fn ($context) => $context['status_code'] === 200
                    && $context['body_size'] === 1024)
            );
    });

    it('logs API response with error status as warning', function () {
        $service = new LogsOperationsTestService;
        $service->logApiResponse('/api/orders', 500);

        Log::shouldHaveReceived('warning')
            ->once()
            ->with(
                '[LogsOperationsTestService] API response: /api/orders',
                Mockery::on(fn ($context) => $context['status_code'] === 500)
            );
    });
});

describe('cache logging', function () {
    it('logs cache hit', function () {
        $service = new LogsOperationsTestService;
        $service->logCacheOperation('get', 'users:123', true);

        Log::shouldHaveReceived('debug')
            ->once()
            ->with(
                '[LogsOperationsTestService] Cache get: users:123',
                Mockery::on(fn ($context) => $context['cache_hit'] === true)
            );
    });

    it('logs cache miss', function () {
        $service = new LogsOperationsTestService;
        $service->logCacheOperation('get', 'users:456', false);

        Log::shouldHaveReceived('debug')
            ->once()
            ->with(
                '[LogsOperationsTestService] Cache get: users:456',
                Mockery::on(fn ($context) => $context['cache_hit'] === false)
            );
    });
});

describe('context enrichment', function () {
    it('includes uuid when available', function () {
        $service = new LogsOperationsTestService;
        $service->logInfo('Test message');

        Log::shouldHaveReceived('info')
            ->once()
            ->with(
                Mockery::any(),
                Mockery::on(fn ($context) => $context['uuid'] === 'test-uuid-123')
            );
    });

    it('includes state keys when available', function () {
        $service = new LogsOperationsTestService;
        $service->logInfo('Test message');

        Log::shouldHaveReceived('info')
            ->once()
            ->with(
                Mockery::any(),
                Mockery::on(fn ($context) => $context['state_keys'] === ['name', 'email'])
            );
    });

    it('works without uuid and state', function () {
        $service = new LogsOperationsBasicService;
        $service->logInfo('Test message');

        Log::shouldHaveReceived('info')
            ->once()
            ->with(
                '[LogsOperationsBasicService] Test message',
                Mockery::on(fn ($context) => $context['class'] === LogsOperationsBasicService::class
                    && $context['user_id'] === 42
                    && ! isset($context['uuid'])
                    && ! isset($context['state_keys']))
            );
    });
});

describe('sensitive data sanitization', function () {
    it('redacts password in array', function () {
        $service = new LogsOperationsTestService;
        $service->logMethodEntry('login', [
            'email' => 'user@example.com',
            'password' => 'mysecretpassword',
        ]);

        Log::shouldHaveReceived('debug')
            ->once()
            ->with(
                Mockery::any(),
                Mockery::on(fn ($context) => $context['params']['email'] === 'user@example.com'
                    && $context['params']['password'] === '***REDACTED***')
            );
    });

    it('redacts api_key in array', function () {
        $service = new LogsOperationsTestService;
        $service->logMethodEntry('callApi', [
            'endpoint' => '/users',
            'api_key' => 'sk_live_123456',
        ]);

        Log::shouldHaveReceived('debug')
            ->once()
            ->with(
                Mockery::any(),
                Mockery::on(fn ($context) => $context['params']['endpoint'] === '/users'
                    && $context['params']['api_key'] === '***REDACTED***')
            );
    });

    it('redacts nested sensitive data', function () {
        $service = new LogsOperationsTestService;
        $service->logMethodEntry('createUser', [
            'user' => [
                'name' => 'John',
                'credentials' => 'secret123',
            ],
        ]);

        Log::shouldHaveReceived('debug')
            ->once()
            ->with(
                Mockery::any(),
                Mockery::on(fn ($context) => $context['params']['user']['name'] === 'John'
                    && $context['params']['user']['credentials'] === '***REDACTED***')
            );
    });

    it('redacts string containing sensitive keyword', function () {
        $service = new LogsOperationsTestService;
        $service->logStateChange('auth', 'old_token_value', 'new_token_value');

        Log::shouldHaveReceived('debug')
            ->once()
            ->with(
                Mockery::any(),
                Mockery::on(fn ($context) => $context['from'] === '***REDACTED***'
                    && $context['to'] === '***REDACTED***')
            );
    });
});

describe('data summarization', function () {
    it('summarizes arrays', function () {
        $service = new LogsOperationsTestService;
        $service->logMethodSuccess('getUsers', [
            'users' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        ]);

        Log::shouldHaveReceived('info')
            ->once()
            ->with(
                Mockery::any(),
                Mockery::on(fn ($context) => $context['result_summary']['users'] === 'array(10 items)')
            );
    });

    it('summarizes objects', function () {
        $service = new LogsOperationsTestService;
        $service->logMethodSuccess('getUser', [
            'user' => new stdClass,
        ]);

        Log::shouldHaveReceived('info')
            ->once()
            ->with(
                Mockery::any(),
                Mockery::on(fn ($context) => $context['result_summary']['user'] === 'object(stdClass)')
            );
    });

    it('truncates long strings', function () {
        $service = new LogsOperationsTestService;
        $longString = str_repeat('a', 150);
        $service->logMethodSuccess('getData', [
            'content' => $longString,
        ]);

        Log::shouldHaveReceived('info')
            ->once()
            ->with(
                Mockery::any(),
                Mockery::on(fn ($context) => strlen($context['result_summary']['content']) === 103
                    && str_ends_with($context['result_summary']['content'], '...'))
            );
    });

    it('keeps short strings as-is', function () {
        $service = new LogsOperationsTestService;
        $service->logMethodSuccess('getData', [
            'status' => 'completed',
        ]);

        Log::shouldHaveReceived('info')
            ->once()
            ->with(
                Mockery::any(),
                Mockery::on(fn ($context) => $context['result_summary']['status'] === 'completed')
            );
    });
});

describe('context helpers', function () {
    it('creates database context', function () {
        $service = new LogsOperationsBasicService;

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('dbContext');
        $method->setAccessible(true);

        $context = $method->invoke($service, 'INSERT', 'users', 123);

        expect($context)->toBe([
            'db_operation' => 'INSERT',
            'table' => 'users',
            'record_id' => 123,
        ]);
    });

    it('creates external service context', function () {
        $service = new LogsOperationsBasicService;

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('externalServiceContext');
        $method->setAccessible(true);

        $context = $method->invoke($service, 'Stripe', 'charge');

        expect($context)->toBe([
            'external_service' => 'Stripe',
            'operation' => 'charge',
        ]);
    });
});

describe('log context', function () {
    it('returns class basename as log context', function () {
        $service = new LogsOperationsTestService;

        expect($service->getLogContext())->toBe('LogsOperationsTestService');
    });
});
