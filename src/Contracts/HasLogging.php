<?php

namespace CleaniqueCoders\Traitify\Contracts;

use Throwable;

interface HasLogging
{
    /**
     * Get the context name for logging
     */
    public function getLogContext(): string;

    /**
     * Log debug message with context
     */
    public function logDebug(string $message, array $context = []): void;

    /**
     * Log info message with context
     */
    public function logInfo(string $message, array $context = []): void;

    /**
     * Log warning message with context
     */
    public function logWarning(string $message, array $context = []): void;

    /**
     * Log error message with context
     */
    public function logError(string $message, array $context = []): void;

    /**
     * Log exception with full stack trace
     */
    public function logException(Throwable $e, string $operation, array $context = []): void;
}
