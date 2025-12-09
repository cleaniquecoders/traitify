<?php

namespace CleaniqueCoders\Traitify\Generators;

use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class UuidGenerator extends AbstractValueGenerator
{
    protected function getDefaultConfig(): array
    {
        return [
            'version' => 'ordered', // 'ordered', 'v4', 'v1', 'v3', 'v5'
            'format' => 'string', // 'string', 'binary', 'hex'
            'prefix' => '',
            'suffix' => '',
            'namespace' => null, // For v3/v5
            'name' => null, // For v3/v5
        ];
    }

    public function generate(array $context = []): mixed
    {
        $version = $this->getConfigValue('version', 'ordered');
        $format = $this->getConfigValue('format', 'string');

        $uuid = match ($version) {
            'v1' => Uuid::uuid1(),
            'v3' => Uuid::uuid3(
                $this->getConfigValue('namespace', Uuid::NAMESPACE_DNS),
                $this->getConfigValue('name', Str::random())
            ),
            'v4' => Uuid::uuid4(),
            'v5' => Uuid::uuid5(
                $this->getConfigValue('namespace', Uuid::NAMESPACE_DNS),
                $this->getConfigValue('name', Str::random())
            ),
            'ordered' => Str::orderedUuid(),
            default => Str::orderedUuid(),
        };

        $value = match ($format) {
            'binary' => $uuid->getBytes(),
            'hex' => str_replace('-', '', $uuid->toString()),
            default => $uuid->toString(),
        };

        $prefix = $this->getConfigValue('prefix', '');
        $suffix = $this->getConfigValue('suffix', '');

        return $prefix.$value.$suffix;
    }

    public function validate(mixed $value, array $context = []): bool
    {
        if (! is_string($value) && ! ($value instanceof \Stringable)) {
            return false;
        }

        $stringValue = (string) $value;

        // Remove prefix/suffix before validation
        $prefix = $this->getConfigValue('prefix', '');
        $suffix = $this->getConfigValue('suffix', '');

        if ($prefix && str_starts_with($stringValue, $prefix)) {
            $stringValue = substr($stringValue, strlen($prefix));
        }

        if ($suffix && str_ends_with($stringValue, $suffix)) {
            $stringValue = substr($stringValue, 0, -strlen($suffix));
        }

        return Str::isUuid($stringValue);
    }
}
