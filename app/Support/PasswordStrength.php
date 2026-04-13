<?php
declare(strict_types=1);

namespace App\Support;

final class PasswordStrength
{
    public function analyze(string $password): array
    {
        $score = 0;
        $feedback = [];

        if (mb_strlen($password) >= 12) {
            $score += 25;
        } else {
            $feedback[] = 'Используйте не менее 12 символов.';
        }

        if (preg_match('/[A-Z]/', $password)) {
            $score += 15;
        } else {
            $feedback[] = 'Добавьте заглавные буквы.';
        }

        if (preg_match('/[a-z]/', $password)) {
            $score += 15;
        } else {
            $feedback[] = 'Добавьте строчные буквы.';
        }

        if (preg_match('/\d/', $password)) {
            $score += 15;
        } else {
            $feedback[] = 'Добавьте цифры.';
        }

        if (preg_match('/[^a-zA-Z0-9]/', $password)) {
            $score += 15;
        } else {
            $feedback[] = 'Добавьте специальные символы.';
        }

        if (!preg_match('/password|qwerty|123456|admin/i', $password)) {
            $score += 15;
        } else {
            $feedback[] = 'Пароль содержит слишком очевидный шаблон.';
        }

        return [
            'score' => min($score, 100),
            'verdict' => $score >= 80 ? 'strong' : ($score >= 55 ? 'medium' : 'weak'),
            'feedback' => $feedback,
        ];
    }
}

