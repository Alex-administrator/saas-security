<?php
declare(strict_types=1);

namespace App\Support;

final class Logger
{
    public static function info(string $message, array $context = []): void
    {
        self::write('info', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('warning', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('error', $message, $context);
    }

    private static function write(string $level, string $message, array $context): void
    {
        $file = storage_path('logs/app.log');
        $directory = dirname($file);

        if (!is_dir($directory)) {
            mkdir($directory, 0770, true);
        }

        $payload = json_encode([
            'timestamp' => gmdate('c'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        file_put_contents($file, $payload . PHP_EOL, FILE_APPEND | LOCK_EX);
        @chmod($file, 0640);
    }
}
