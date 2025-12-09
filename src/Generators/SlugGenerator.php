<?php

namespace CleaniqueCoders\Traitify\Generators;

use Illuminate\Support\Str;

class SlugGenerator extends AbstractValueGenerator
{
    protected function getDefaultConfig(): array
    {
        return [
            'separator' => '-',
            'language' => 'en',
            'dictionary' => ['@' => 'at'],
            'lowercase' => true,
            'prefix' => '',
            'suffix' => '',
            'max_length' => null,
            'unique' => false, // Whether to ensure uniqueness
        ];
    }

    public function generate(array $context = []): string
    {
        $source = $context['source'] ?? '';

        if (empty($source)) {
            return '';
        }

        $separator = $this->getConfigValue('separator', '-');
        $language = $this->getConfigValue('language', 'en');
        $dictionary = $this->getConfigValue('dictionary', ['@' => 'at']);

        $slug = Str::slug($source, $separator, $language, $dictionary);

        $lowercase = $this->getConfigValue('lowercase', true);
        if (! $lowercase) {
            // Preserve case but still apply slug transformation
            $slug = preg_replace('/[^A-Za-z0-9-]+/', $separator, $source);
            $slug = preg_replace('/'.$separator.'+/', $separator, $slug);
            $slug = trim($slug, $separator);
        }

        $prefix = $this->getConfigValue('prefix', '');
        $suffix = $this->getConfigValue('suffix', '');

        $slug = $prefix.$slug.$suffix;

        $maxLength = $this->getConfigValue('max_length');
        if ($maxLength && strlen($slug) > $maxLength) {
            $slug = substr($slug, 0, $maxLength);
            // Trim trailing separator
            $slug = rtrim($slug, $separator);
        }

        // Handle uniqueness if required
        if ($this->getConfigValue('unique', false) && isset($context['model'])) {
            $slug = $this->ensureUnique($slug, $context);
        }

        return $slug;
    }

    public function validate(mixed $value, array $context = []): bool
    {
        if (! is_string($value)) {
            return false;
        }

        $separator = $this->getConfigValue('separator', '-');

        // Check if it matches slug pattern
        $pattern = '/^[a-z0-9'.preg_quote($separator, '/').']+$/i';

        return preg_match($pattern, $value) === 1;
    }

    /**
     * Ensure slug uniqueness by appending incremental numbers.
     */
    protected function ensureUnique(string $slug, array $context): string
    {
        $model = $context['model'];
        $column = $context['column'] ?? 'slug';
        $separator = $this->getConfigValue('separator', '-');

        $originalSlug = $slug;
        $counter = 1;

        while ($model->newQuery()->where($column, $slug)->exists()) {
            $slug = $originalSlug.$separator.$counter;
            $counter++;
        }

        return $slug;
    }
}
