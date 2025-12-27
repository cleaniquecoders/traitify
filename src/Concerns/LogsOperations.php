<?php

namespace CleaniqueCoders\Traitify\Concerns;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * LogsOperations
 *
 * Unified trait for consistent logging across Actions, Services, and Livewire Forms.
 * Provides structured logging with context enrichment and sensitive data sanitization.
 */
trait LogsOperations
{
    /**
     * Get the context name for logging
     */
    public function getLogContext(): string
    {
        return class_basename(static::class);
    }

    /**
     * Log debug message with context
     */
    public function logDebug(string $message, array $context = []): void
    {
        Log::debug($this->formatLogMessage($message), $this->enrichContext($context));
    }

    /**
     * Log info message with context
     */
    public function logInfo(string $message, array $context = []): void
    {
        Log::info($this->formatLogMessage($message), $this->enrichContext($context));
    }

    /**
     * Log warning message with context
     */
    public function logWarning(string $message, array $context = []): void
    {
        Log::warning($this->formatLogMessage($message), $this->enrichContext($context));
    }

    /**
     * Log error message with context
     */
    public function logError(string $message, array $context = []): void
    {
        Log::error($this->formatLogMessage($message), $this->enrichContext($context));
    }

    /**
     * Log exception with full stack trace
     */
    public function logException(Throwable $e, string $operation, array $context = []): void
    {
        Log::error($this->formatLogMessage("{$operation} failed"), array_merge(
            $this->enrichContext($context),
            [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]
        ));
    }

    /**
     * Log operation start
     */
    public function logOperationStart(string $operation, array $context = []): void
    {
        $this->logInfo("{$operation} started", $context);
    }

    /**
     * Log operation success
     */
    public function logOperationSuccess(string $operation, array $context = []): void
    {
        $this->logInfo("{$operation} completed successfully", $context);
    }

    /**
     * Log operation failure
     */
    public function logOperationFailure(string $operation, array $context = []): void
    {
        $this->logError("{$operation} failed", $context);
    }

    /**
     * Log method entry
     */
    public function logMethodEntry(string $method, array $params = []): void
    {
        $this->logDebug("{$method}() called", [
            'params' => $this->sanitize($params),
        ]);
    }

    /**
     * Log method success
     */
    public function logMethodSuccess(string $method, array $result = []): void
    {
        $this->logInfo("{$method}() completed successfully", [
            'result_summary' => $this->summarize($result),
        ]);
    }

    /**
     * Log method failure
     */
    public function logMethodFailure(string $method, array $context = []): void
    {
        $this->logError("{$method}() failed", $context);
    }

    /**
     * Log validation attempt
     */
    public function logValidation(array $data, ?array $errors = null): void
    {
        if ($errors === null) {
            $this->logDebug('Validation passed', ['data_keys' => array_keys($data)]);
        } else {
            $this->logWarning('Validation failed', [
                'data_keys' => array_keys($data),
                'errors' => $errors,
            ]);
        }
    }

    /**
     * Log state change
     */
    public function logStateChange(string $field, mixed $from, mixed $to): void
    {
        $this->logDebug('State changed', [
            'field' => $field,
            'from' => $this->sanitize($from),
            'to' => $this->sanitize($to),
        ]);
    }

    /**
     * Log external API call
     */
    public function logApiCall(string $method, string $endpoint, array $context = []): void
    {
        $this->logDebug("API call: {$method} {$endpoint}", $context);
    }

    /**
     * Log external API response
     */
    public function logApiResponse(string $endpoint, int $statusCode, array $context = []): void
    {
        $logLevel = $statusCode >= 400 ? 'warning' : 'debug';
        Log::{$logLevel}($this->formatLogMessage("API response: {$endpoint}"), array_merge(
            $this->enrichContext($context),
            ['status_code' => $statusCode]
        ));
    }

    /**
     * Log cache operation
     */
    public function logCacheOperation(string $operation, string $key, bool $hit = true): void
    {
        $this->logDebug("Cache {$operation}: {$key}", [
            'cache_hit' => $hit,
        ]);
    }

    /**
     * Format log message with context
     */
    protected function formatLogMessage(string $message): string
    {
        return "[{$this->getLogContext()}] {$message}";
    }

    /**
     * Enrich context with common data
     */
    protected function enrichContext(array $context): array
    {
        $enriched = [
            'class' => static::class,
            'user_id' => Auth::id(),
        ];

        // Add UUID if available
        if (property_exists($this, 'uuid') && $this->uuid) {
            $enriched['uuid'] = $this->uuid;
        }

        // Add state keys if available (for Livewire forms)
        if (property_exists($this, 'state') && is_array($this->state)) {
            $enriched['state_keys'] = array_keys($this->state);
        }

        return array_merge($enriched, $context);
    }

    /**
     * Sanitize sensitive data for logging
     */
    protected function sanitize(mixed $value): mixed
    {
        $sensitiveKeys = [
            'password',
            'password_confirmation',
            'token',
            'secret',
            'api_key',
            'key',
            'apikey',
            'authorization',
            'bearer',
            'credentials',
        ];

        if (is_string($value)) {
            foreach ($sensitiveKeys as $pattern) {
                if (stripos($value, $pattern) !== false) {
                    return '***REDACTED***';
                }
            }

            return $value;
        }

        if (is_array($value)) {
            foreach ($sensitiveKeys as $key) {
                if (isset($value[$key])) {
                    $value[$key] = '***REDACTED***';
                }
            }

            // Recursively sanitize nested arrays
            foreach ($value as $k => $v) {
                if (is_array($v)) {
                    $value[$k] = $this->sanitize($v);
                }
            }

            return $value;
        }

        return $value;
    }

    /**
     * Summarize data for logging (avoid logging large data)
     */
    protected function summarize(array $data): array
    {
        $summary = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $summary[$key] = 'array('.count($value).' items)';
            } elseif (is_object($value)) {
                $summary[$key] = 'object('.get_class($value).')';
            } elseif (is_string($value) && strlen($value) > 100) {
                $summary[$key] = substr($value, 0, 100).'...';
            } else {
                $summary[$key] = $value;
            }
        }

        return $summary;
    }

    /**
     * Create a logging context for database operations
     */
    protected function dbContext(string $operation, string $table, ?int $recordId = null): array
    {
        return [
            'db_operation' => $operation,
            'table' => $table,
            'record_id' => $recordId,
        ];
    }

    /**
     * Create a logging context for external service calls
     */
    protected function externalServiceContext(string $service, string $operation): array
    {
        return [
            'external_service' => $service,
            'operation' => $operation,
        ];
    }
}
