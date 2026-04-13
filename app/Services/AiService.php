<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\AiRequestRepository;

final class AiService
{
    public function analyzeText(string $text, int $organizationId, int $userId): array
    {
        $analysis = $this->heuristicAnalysis($text);

        if ((bool) config('ai.enabled', false) && (string) config('ai.api_key', '') !== '') {
            $analysis['external_summary'] = $this->remoteSummary($text);
        } else {
            $analysis['external_summary'] = null;
        }

        $analysis['engine'] = $analysis['external_summary'] ? 'hybrid' : 'heuristic';

        (new AiRequestRepository())->log([
            'organization_id' => $organizationId,
            'user_id' => $userId,
            'mode' => 'text',
            'input_hash' => hash('sha256', $text),
            'input_excerpt' => sprintf('[redacted:%d chars]', mb_strlen($text)),
            'result' => $analysis,
            'status' => 'completed',
        ]);

        return $analysis;
    }

    private function heuristicAnalysis(string $text): array
    {
        $score = 0;
        $markers = [];

        $patterns = [
            'urgent' => '/urgent|срочно|немедленно/i',
            'credential' => '/password|парол|login|verify account|подтвердите/i',
            'money' => '/payment|invoice|счет|оплата|bank/i',
            'attachment' => '/attachment|вложени|download|скачать/i',
            'link' => '/click here|перейдите|open the link|ссылка/i',
        ];

        foreach ($patterns as $name => $pattern) {
            if (preg_match($pattern, $text)) {
                $score += 18;
                $markers[] = $name;
            }
        }

        if (preg_match_all('/https?:\/\/[^\s]+/i', $text) > 3) {
            $score += 10;
            $markers[] = 'many_links';
        }

        return [
            'risk_score' => min($score, 100),
            'risk_level' => $score >= 60 ? 'high' : ($score >= 30 ? 'medium' : 'low'),
            'markers' => $markers,
            'summary' => $score >= 60
                ? 'Текст содержит несколько сильных признаков социальной инженерии.'
                : ($score >= 30 ? 'Есть отдельные подозрительные маркеры, требуется ручная проверка.' : 'Явных признаков повышенного риска немного.'),
            'recommendations' => [
                'Проверьте домен отправителя и совпадение контекста сообщения.',
                'Не переходите по ссылкам без проверки и не вводите учетные данные.',
                'Если письмо кажется подозрительным, сообщите в security-команду.',
            ],
        ];
    }

    private function remoteSummary(string $text): ?string
    {
        if (!function_exists('curl_init')) {
            return null;
        }

        $baseUrl = rtrim((string) config('ai.base_url'), '/');
        $scheme = strtolower((string) (parse_url($baseUrl, PHP_URL_SCHEME) ?? ''));
        if ($scheme !== 'https') {
            return null;
        }

        $payload = [
            'model' => (string) config('ai.model'),
            'messages' => [
                ['role' => 'system', 'content' => 'You analyze suspicious corporate text and return a short Russian summary with safe recommendations.'],
                ['role' => 'user', 'content' => mb_substr($text, 0, 6000)],
            ],
            'temperature' => 0.2,
        ];

        $encodedPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($encodedPayload)) {
            return null;
        }

        $ch = curl_init($baseUrl . '/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . config('ai.api_key'),
            ],
            CURLOPT_POSTFIELDS => $encodedPayload,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if (!is_string($response) || $status >= 400) {
            return null;
        }

        $decoded = json_decode($response, true);
        return $decoded['choices'][0]['message']['content'] ?? null;
    }
}
