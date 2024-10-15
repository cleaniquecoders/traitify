<?php

namespace CleaniqueCoders\Traitify\Contracts;

interface Enum
{
    /**
     * Get the label for the enum case.
     */
    public function label(): string;

    /**
     * Get the description for the enum case.
     */
    public function description(): string;

    /**
     * Get an array of enum values.
     */
    public static function values(): array;

    /**
     * Get an array of enum labels.
     */
    public static function labels(): array;

    /**
     * Generate an array of options with value, label, and description for select inputs.
     */
    public static function options(): array;
}
