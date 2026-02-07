<?php

namespace App\Enums;

use Illuminate\Support\Collection;

trait EnumTraits
{
    /**
     * Get all enum cases as collection.
     */
    public static function all(): Collection
    {
        return collect(self::cases());
    }

    /**
     * Get all enum values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all enum names.
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    /**
     * Try to get enum from value.
     */
    public static function tryFromValue(mixed $value): ?self
    {
        return self::tryFrom($value);
    }

    /**
     * Check if value exists in enum.
     */
    public static function hasValue(mixed $value): bool
    {
        return in_array($value, self::values(), true);
    }
}