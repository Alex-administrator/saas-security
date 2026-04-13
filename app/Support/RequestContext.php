<?php
declare(strict_types=1);

namespace App\Support;

final class RequestContext
{
    private static array $values = [];

    public static function set(string $key, mixed $value): void
    {
        self::$values[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$values[$key] ?? $default;
    }
}

