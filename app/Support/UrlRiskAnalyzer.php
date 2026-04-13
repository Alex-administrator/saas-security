<?php
declare(strict_types=1);

namespace App\Support;

final class UrlRiskAnalyzer
{
    public function analyze(string $url): array
    {
        $parsed = parse_url($url);
        $host = (string) ($parsed['host'] ?? '');
        $scheme = strtolower((string) ($parsed['scheme'] ?? ''));
        $reasons = [];
        $score = 0;

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return [
                'score' => 100,
                'risk' => 'high',
                'reasons' => ['URL не прошел базовую валидацию.'],
            ];
        }

        if ($scheme !== 'https') {
            $score += 20;
            $reasons[] = 'Используется незащищенная схема.';
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $score += 20;
            $reasons[] = 'URL указывает на IP-адрес вместо доменного имени.';
        }

        if (str_contains($host, 'xn--')) {
            $score += 20;
            $reasons[] = 'Обнаружен punycode-домен.';
        }

        if (substr_count($host, '.') >= 3) {
            $score += 10;
            $reasons[] = 'Слишком глубокая иерархия поддоменов.';
        }

        if (preg_match('/login|secure|verify|update|pay/i', $url)) {
            $score += 15;
            $reasons[] = 'В URL есть социально-инженерные маркеры.';
        }

        if (strlen($url) > 120) {
            $score += 10;
            $reasons[] = 'URL длиннее обычного.';
        }

        return [
            'score' => min($score, 100),
            'risk' => $score >= 60 ? 'high' : ($score >= 30 ? 'medium' : 'low'),
            'host' => $host,
            'scheme' => $scheme,
            'reasons' => $reasons,
        ];
    }
}
