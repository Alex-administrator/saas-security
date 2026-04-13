<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\ToolRunRepository;
use App\Support\PasswordStrength;
use App\Support\UrlRiskAnalyzer;

final class ToolService
{
    public function passwordStrength(string $password, ?int $organizationId, ?int $userId): array
    {
        $result = (new PasswordStrength())->analyze($password);

        (new ToolRunRepository())->log([
            'organization_id' => $organizationId,
            'user_id' => $userId,
            'tool_name' => 'password_strength',
            'input_value' => '[redacted]',
            'result' => $result,
        ]);

        return $result;
    }

    public function urlAnalyze(string $url, ?int $organizationId, ?int $userId): array
    {
        $result = (new UrlRiskAnalyzer())->analyze($url);

        (new ToolRunRepository())->log([
            'organization_id' => $organizationId,
            'user_id' => $userId,
            'tool_name' => 'url_analyze',
            'input_value' => $this->redactedUrlForAudit($url),
            'result' => $result,
        ]);

        return $result;
    }

    private function redactedUrlForAudit(string $url): string
    {
        $scheme = strtolower((string) (parse_url($url, PHP_URL_SCHEME) ?? ''));
        $host = (string) (parse_url($url, PHP_URL_HOST) ?? '');

        if ($host === '') {
            return '[redacted]';
        }

        return sprintf('[redacted:%s://%s]', $scheme !== '' ? $scheme : 'url', $host);
    }
}
