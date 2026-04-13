<?php
declare(strict_types=1);

namespace App\Support;

final class FileCache
{
    public function get(string $key, mixed $default = null): mixed
    {
        $file = $this->path($key);
        if (!is_file($file)) {
            return $default;
        }

        $payload = json_decode((string) file_get_contents($file), true);
        if (!is_array($payload)) {
            @unlink($file);
            return $default;
        }

        if (($payload['expires_at'] ?? 0) !== 0 && $payload['expires_at'] < time()) {
            @unlink($file);
            return $default;
        }

        return $payload['value'] ?? $default;
    }

    public function put(string $key, mixed $value, int $seconds = 0): void
    {
        $file = $this->path($key);
        $directory = dirname($file);

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $payload = json_encode([
            'expires_at' => $seconds > 0 ? time() + $seconds : 0,
            'value' => $value,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (!is_string($payload)) {
            return;
        }

        file_put_contents($file, $payload, LOCK_EX);
        @chmod($file, 0640);
    }

    private function path(string $key): string
    {
        return storage_path('cache/data/' . sha1($key) . '.cache');
    }
}
