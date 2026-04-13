<?php
declare(strict_types=1);

namespace App\Support;

final class RateLimiter
{
    public function hit(string $key, int $maxAttempts, int $windowSeconds): array
    {
        $file = storage_path('cache/data/' . sha1('rl:' . $key) . '.rl');
        $dir  = dirname($file);

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $fp = fopen($file, 'c+');
        if ($fp === false) {
            return ['allowed' => true, 'remaining' => $maxAttempts, 'reset_at' => time() + $windowSeconds];
        }

        flock($fp, LOCK_EX);

        $content = stream_get_contents($fp);
        $data    = $content !== '' ? json_decode($content, true) : null;
        $now     = time();

        if (!is_array($data) || ($data['reset_at'] ?? 0) < $now) {
            $data = ['count' => 0, 'reset_at' => $now + $windowSeconds];
        }

        $data['count']++;

        rewind($fp);
        ftruncate($fp, 0);
        fwrite($fp, (string) json_encode($data, JSON_UNESCAPED_UNICODE));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);

        return [
            'allowed'   => $data['count'] <= $maxAttempts,
            'remaining' => max($maxAttempts - $data['count'], 0),
            'reset_at'  => $data['reset_at'],
        ];
    }
}

