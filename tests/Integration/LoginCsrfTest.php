<?php
declare(strict_types=1);

/**
 * Integration-тест: CSRF-токен и защита логина.
 *
 * Тестирует Csrf и Validator напрямую без HTTP-запросов.
 * Для полноценного end-to-end теста запусти docker compose и используй curl.
 */

require __DIR__ . '/../../bootstrap/app.php';

use App\Support\Csrf;
use App\Support\Validator;
use App\Support\ValidationException;

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

function throws(string $label, callable $fn): void
{
    global $passed, $failed;
    try {
        $fn();
        fwrite(STDERR, "  [FAIL] {$label} — ожидалось исключение\n");
        $failed++;
    } catch (ValidationException) {
        fwrite(STDOUT, "  [OK] {$label}\n");
        $passed++;
    }
}

// Запускаем сессию для тестов CSRF
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

fwrite(STDOUT, "=== LoginCsrfTest ===\n\n");

// --- CSRF ---
fwrite(STDOUT, "-- CSRF --\n");

$token1 = Csrf::token();
$token2 = Csrf::token();
ok('token() возвращает строку', is_string($token1));
ok('token() идемпотентен (один токен на сессию)', $token1 === $token2);
ok('token() длина 64 символа (hex от 32 байт)', mb_strlen($token1) === 64);
ok('validate() с верным токеном', Csrf::validate($token1) === true);
ok('validate() с неверным токеном', Csrf::validate('wrong') === false);
ok('validate() с null', Csrf::validate(null) === false);
ok('validate() с пустой строкой', Csrf::validate('') === false);

// Timing-safe: разные строки одинаковой длины
$fakeToken = str_repeat('a', 64);
ok('validate() устойчив к timing-атаке (разный токен)', Csrf::validate($fakeToken) === false);

// --- Валидация логин-формы ---
fwrite(STDOUT, "\n-- Login form validation --\n");

ok('корректный логин проходит', (function () {
    Validator::validate(
        ['email' => 'user@example.com', 'password' => 'SecurePass1'],
        ['email' => 'required|email', 'password' => 'required|string|min:8|max:1024']
    );
    return true;
})());

throws('пустой email — ошибка', fn() => Validator::validate(
    ['email' => '', 'password' => 'SecurePass1'],
    ['email' => 'required|email', 'password' => 'required|string|min:8|max:1024']
));

throws('невалидный email — ошибка', fn() => Validator::validate(
    ['email' => 'notanemail', 'password' => 'SecurePass1'],
    ['email' => 'required|email', 'password' => 'required|string|min:8|max:1024']
));

throws('пароль короче 8 символов — ошибка', fn() => Validator::validate(
    ['email' => 'user@example.com', 'password' => 'short'],
    ['email' => 'required|email', 'password' => 'required|string|min:8|max:1024']
));

throws('пароль длиннее 1024 символов — блокируется (bcrypt DoS)', fn() => Validator::validate(
    ['email' => 'user@example.com', 'password' => str_repeat('a', 1025)],
    ['email' => 'required|email', 'password' => 'required|string|min:8|max:1024']
));

throws('пустой пароль — ошибка', fn() => Validator::validate(
    ['email' => 'user@example.com', 'password' => ''],
    ['email' => 'required|email', 'password' => 'required|string|min:8|max:1024']
));

fwrite(STDOUT, "\nРезультат: {$passed} пройдено, {$failed} провалено\n");
exit($failed > 0 ? 1 : 0);
