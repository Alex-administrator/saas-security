<?php
declare(strict_types=1);

require __DIR__ . '/../../bootstrap/app.php';

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

fwrite(STDOUT, "=== ValidatorTest ===\n");

// required
throws('required: пустая строка', fn() => Validator::validate(['f' => ''], ['f' => 'required']));
throws('required: null', fn() => Validator::validate(['f' => null], ['f' => 'required']));
ok('required: значение есть', (function () {
    Validator::validate(['f' => 'hello'], ['f' => 'required']);
    return true;
})());

// email
throws('email: невалидный', fn() => Validator::validate(['e' => 'not-email'], ['e' => 'required|email']));
ok('email: валидный', (function () {
    Validator::validate(['e' => 'user@example.com'], ['e' => 'required|email']);
    return true;
})());

// min / max
throws('min:8 нарушен', fn() => Validator::validate(['p' => 'short'], ['p' => 'required|min:8']));
ok('min:4 пройден', (function () {
    Validator::validate(['p' => 'pass'], ['p' => 'required|min:4']);
    return true;
})());
throws('max:5 нарушен', fn() => Validator::validate(['p' => 'toolongvalue'], ['p' => 'required|max:5']));
ok('max:1024 пройден', (function () {
    Validator::validate(['p' => str_repeat('a', 100)], ['p' => 'required|max:1024']);
    return true;
})());

// in
throws('in: недопустимое значение', fn() => Validator::validate(['r' => 'owner'], ['r' => 'required|in:admin,user']));
ok('in: допустимое значение', (function () {
    Validator::validate(['r' => 'admin'], ['r' => 'required|in:admin,user']);
    return true;
})());

// url
throws('url: невалидный', fn() => Validator::validate(['u' => 'not a url'], ['u' => 'required|url']));
ok('url: валидный', (function () {
    Validator::validate(['u' => 'https://example.com'], ['u' => 'required|url']);
    return true;
})());

// необязательное поле — правила пропускаются при пустом значении
ok('необязательное поле пустое — ошибок нет', (function () {
    Validator::validate(['e' => ''], ['e' => 'email']);
    return true;
})());

fwrite(STDOUT, "\nРезультат: {$passed} пройдено, {$failed} провалено\n");
exit($failed > 0 ? 1 : 0);
