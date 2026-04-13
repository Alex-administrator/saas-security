<?php
declare(strict_types=1);

require __DIR__ . '/../../bootstrap/app.php';

use App\Support\PasswordStrength;

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

fwrite(STDOUT, "=== PasswordStrengthTest ===\n");

$ps = new PasswordStrength();

// Слабый пароль
$r = $ps->analyze('pass');
ok('короткий пароль — weak', $r['verdict'] === 'weak');
ok('короткий пароль — score < 55', $r['score'] < 55);
ok('есть feedback про длину', in_array('Используйте не менее 12 символов.', $r['feedback'], true));

// Средний пароль: есть буквы, цифра, нет спецсимвола, нет паттерна — score=60 → medium
$r = $ps->analyze('SecureX1');
ok('средний пароль — medium', $r['verdict'] === 'medium');
ok('нет спецсимвола — feedback об этом', in_array('Добавьте специальные символы.', $r['feedback'], true));

// Сильный пароль
$r = $ps->analyze('C0mpl3x!P@ssword');
ok('сильный пароль — strong', $r['verdict'] === 'strong');
ok('сильный пароль — score >= 80', $r['score'] >= 80);
ok('сильный пароль — нет feedback', $r['feedback'] === []);

// Очевидный шаблон
$r = $ps->analyze('password123');
ok('содержит "password" — feedback об этом', in_array('Пароль содержит слишком очевидный шаблон.', $r['feedback'], true));

$r = $ps->analyze('admin123456');
ok('содержит "admin" — слабый', $r['verdict'] === 'weak');

// Score не превышает 100
$r = $ps->analyze('C0mpl3x!P@sswordXXXXLong');
ok('score не превышает 100', $r['score'] <= 100);

fwrite(STDOUT, "\nРезультат: {$passed} пройдено, {$failed} провалено\n");
exit($failed > 0 ? 1 : 0);
