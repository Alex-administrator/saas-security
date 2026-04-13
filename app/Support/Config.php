<?php
declare(strict_types=1);

namespace App\Support;

final class Config
{
    private static array $items = [];

    public static function load(string $directory): void
    {
        $files = glob(rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.php') ?: [];

        foreach ($files as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            self::$items[$name] = require $file;
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = self::$items;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}

