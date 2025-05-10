<?php

namespace Innoboxrr\Traits;

trait EnumTrait
{
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getKeys(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function getKey(string $value): ?string
    {
        return array_search($value, self::getValues(), true) ?: null;
    }

    public static function getValue(string $key): ?string
    {
        try {
            return self::from($key)->value ?? null;
        } catch (\ValueError) {
            return null;
        }
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, self::getValues(), true);
    }

    public static function isValidKey(string $key): bool
    {
        return in_array($key, self::getKeys(), true);
    }

    public static function isValidValue(string $value): bool
    {
        return self::isValid($value);
    }

    public static function isValidKeyValue(string $key, string $value): bool
    {
        return self::isValidKey($key) && self::getValue($key) === $value;
    }
}
