<?php
declare(strict_types=1);

require __DIR__ . '/../../bootstrap/app.php';

use App\Support\RateLimiter;

$passed = 0;
$failed = 0;

function ok(string $label, bool $condition): void
{
    global $passed, $failed;
    if ($condition) {
        fwrite(STDOUT, "  [OK] {$label}\n");
        $passed++;
    } else {
        fwrite(STDERR, "  [FAIL] {$label}\n");
        $failed++;
    }
}

fwrite(STDOUT, "=== RateLimiterTest ===\n");

$limiter = new RateLimiter();
$key = 'test:ratelimit:' . uniqid('', true);

// Первый запрос — разрешён
$r = $limiter->hit($key, 3, 60);
ok('1-й запрос: allowed=true', $r['allowed'] === true);
ok('1-й запрос: remaining=2', $r['remaining'] === 2);
ok('reset_at в будущем', $r['reset_at'] > time());

// Второй
$r = $limiter->hit($key, 3, 60);
ok('2-й запрос: allowed=true', $r['allowed'] === true);
ok('2-й запрос: remaining=1', $r['remaining'] === 1);

// Третий — последний разрешённый
$r = $limiter->hit($key, 3, 60);
ok('3-й запрос: allowed=true', $r['allowed'] === true);
ok('3-й запрос: remaining=0', $r['remaining'] === 0);

// Четвёртый — превышение
$r = $limiter->hit($key, 3, 60);
ok('4-й запрос: allowed=false', $r['allowed'] === false);
ok('4-й запрос: remaining=0', $r['remaining'] === 0);

// Разные ключи не мешают друг другу
$key2 = 'test:ratelimit:' . uniqid('', true);
$r2 = $limiter->hit($key2, 3, 60);
ok('другой ключ — независимый счётчик', $r2['allowed'] === true);
ok('другой ключ: remaining=2', $r2['remaining'] === 2);

// Окно с прошедшим временем — счётчик сбрасывается
$keyExpired = 'test:ratelimit:expired:' . uniqid('', true);
// Пишем файл напрямую с истёкшим reset_at
$cacheFile = storage_path('cache/data/' . sha1('rl:' . $keyExpired) . '.rl');
@mkdir(dirname($cacheFile), 0775, true);
file_put_contents($cacheFile, json_encode(['count' => 99, 'reset_at' => time() - 1]));
$r = $limiter->hit($keyExpired, 3, 60);
ok('истёкшее окно: счётчик сброшен, allowed=true', $r['allowed'] === true);
ok('истёкшее окно: remaining=2', $r['remaining'] === 2);

// Очистка тестовых файлов
foreach ([$key, $key2, $keyExpired] as $k) {
    @unlink(storage_path('cache/data/' . sha1('rl:' . $k) . '.rl'));
}

fwrite(STDOUT, "\nРезультат: {$passed} пройдено, {$failed} провалено\n");
exit($failed > 0 ? 1 : 0);
