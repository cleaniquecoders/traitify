<?php

namespace CleaniqueCoders\Traitify\Contracts;

interface ValueGenerator
{
    /**
     * Generate a new value.
     *
     * @param  array<string, mixed>  $context  Additional context (model, column, etc.)
     * @return mixed The generated value
     */
    public function generate(array $context = []): mixed;

    /**
     * Validate a generated or existing value.
     *
     * @param  mixed  $value  The value to validate
     * @param  array<string, mixed>  $context  Additional context
     * @return bool True if valid, false otherwise
     */
    public function validate(mixed $value, array $context = []): bool;

    /**
     * Get the configuration for this generator.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array;

    /**
     * Set the configuration for this generator.
     *
     * @param  array<string, mixed>  $config
     */
    public function setConfig(array $config): self;
}
