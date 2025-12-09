<?php

namespace CleaniqueCoders\Traitify\Generators;

use CleaniqueCoders\Traitify\Contracts\ValueGenerator;

abstract class AbstractValueGenerator implements ValueGenerator
{
    protected array $config = [];

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
    }

    /**
     * Get default configuration for this generator.
     *
     * @return array<string, mixed>
     */
    abstract protected function getDefaultConfig(): array;

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): self
    {
        $this->config = array_merge($this->config, $config);

        return $this;
    }

    /**
     * Get a configuration value with dot notation support.
     */
    protected function getConfigValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }
}
