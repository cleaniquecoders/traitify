<?php

namespace CleaniqueCoders\Traitify\Concerns;

use CleaniqueCoders\Traitify\Contracts\ValueGenerator;

trait HasGeneratorResolver
{
    /**
     * Resolve a generator instance.
     *
     * @param  string  $type  The generator type (token, uuid, slug)
     * @param  string|null  $propertyName  Model property name for custom generator
     * @param  string|null  $configPropertyName  Model property name for generator config
     */
    protected function resolveGenerator(
        string $type,
        ?string $propertyName = null,
        ?string $configPropertyName = null
    ): ValueGenerator {
        // 1. Check model property for custom generator class
        if ($propertyName && isset($this->{$propertyName})) {
            $generatorClass = $this->{$propertyName};

            if (is_string($generatorClass)) {
                return $this->instantiateGenerator($generatorClass, $configPropertyName);
            }

            // If it's already an instance
            if ($generatorClass instanceof ValueGenerator) {
                return $generatorClass;
            }
        }

        // 2. Check config for generator
        $configKey = "traitify.generators.{$type}";
        $generatorConfig = config($configKey);

        if ($generatorConfig && isset($generatorConfig['class'])) {
            $class = $generatorConfig['class'];
            $config = $generatorConfig['config'] ?? [];

            return new $class($config);
        }

        // 3. Fallback to default generator
        $defaultClass = $this->getDefaultGeneratorClass($type);

        return new $defaultClass;
    }

    /**
     * Instantiate a generator with optional config from model property.
     */
    protected function instantiateGenerator(
        string $class,
        ?string $configPropertyName = null
    ): ValueGenerator {
        $config = [];

        if ($configPropertyName && isset($this->{$configPropertyName})) {
            $config = $this->{$configPropertyName};
        }

        return new $class($config);
    }

    /**
     * Get default generator class for a type.
     */
    protected function getDefaultGeneratorClass(string $type): string
    {
        return match ($type) {
            'token' => \CleaniqueCoders\Traitify\Generators\TokenGenerator::class,
            'uuid' => \CleaniqueCoders\Traitify\Generators\UuidGenerator::class,
            'slug' => \CleaniqueCoders\Traitify\Generators\SlugGenerator::class,
            default => throw new \InvalidArgumentException("Unknown generator type: {$type}"),
        };
    }
}
