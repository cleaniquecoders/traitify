<?php

namespace CleaniqueCoders\Traitify\Concerns;

trait InteractsWithEnum
{
    /**
     * Get an array of enum values.
     */
    public static function values(): array
    {
        return array_map(
            fn ($case) => $case->value, self::cases()
        );
    }

    /**
     * Get an array of enum label.
     */
    public static function labels(): array
    {
        return array_map(
            fn ($case) => $case->label(), self::cases()
        );
    }

    /**
     * Generate an array of options with value, label, and description for select inputs.
     */
    public static function options(): array
    {
        return array_map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'description' => $case->description(),
        ], self::cases());
    }
}
