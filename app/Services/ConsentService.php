<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\ConsentRepository;
use RuntimeException;

final class ConsentService
{
    public function record(array $data): int
    {
        $repository = new ConsentRepository();
        $version = $repository->activeVersion((string) ($data['type'] ?? 'privacy'));

        if ($version === null) {
            throw new RuntimeException('Активная версия согласия не найдена.');
        }

        return $repository->log([
            'organization_id' => $data['organization_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'subject_email' => $data['subject_email'] ?? null,
            'consent_version_id' => $version['id'],
            'ip' => $data['ip'],
            'user_agent' => $data['user_agent'],
        ]);
    }
}
