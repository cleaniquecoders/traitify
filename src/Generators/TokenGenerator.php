<?php

namespace CleaniqueCoders\Traitify\Generators;

use Illuminate\Support\Str;

class TokenGenerator extends AbstractValueGenerator
{
    protected function getDefaultConfig(): array
    {
        return [
            'length' => 128,
            'pool' => 'auto', // 'auto', 'alpha', 'alphanumeric', 'numeric', 'hex'
            'prefix' => '',
            'suffix' => '',
            'uppercase' => false,
        ];
    }

    public function generate(array $context = []): string
    {
        $length = $this->getConfigValue('length', 128);
        $pool = $this->getConfigValue('pool', 'auto');

        $token = match ($pool) {
            'alpha' => $this->generateWithPool($length, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'),
            'numeric' => $this->generateWithPool($length, '0123456789'),
            'hex' => substr(bin2hex(random_bytes(ceil($length / 2))), 0, $length),
            'alphanumeric' => $this->generateWithPool($length, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'),
            default => Str::random($length),
        };

        $prefix = $this->getConfigValue('prefix', '');
        $suffix = $this->getConfigValue('suffix', '');
        $uppercase = $this->getConfigValue('uppercase', false);

        $token = $prefix.$token.$suffix;

        return $uppercase ? strtoupper($token) : $token;
    }

    public function validate(mixed $value, array $context = []): bool
    {
        if (! is_string($value)) {
            return false;
        }

        $length = $this->getConfigValue('length', 128);
        $prefix = $this->getConfigValue('prefix', '');
        $suffix = $this->getConfigValue('suffix', '');

        $expectedLength = $length + strlen($prefix) + strlen($suffix);

        return strlen($value) === $expectedLength;
    }

    /**
     * Generate a random string from a specific character pool.
     */
    protected function generateWithPool(int $length, string $characters): string
    {
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
