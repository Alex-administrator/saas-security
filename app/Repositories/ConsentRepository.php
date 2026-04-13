<?php
declare(strict_types=1);

namespace App\Repositories;

final class ConsentRepository extends BaseRepository
{
    public function activeVersion(string $type = 'privacy'): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM consent_versions WHERE type = :type AND is_active = 1 ORDER BY id DESC LIMIT 1',
            ['type' => $type]
        );
    }

    public function log(array $data): int
    {
        $this->execute(
            'INSERT INTO consents (organization_id, user_id, subject_email, consent_version_id, ip, user_agent, created_at)
             VALUES (:organization_id, :user_id, :subject_email, :consent_version_id, :ip, :user_agent, UTC_TIMESTAMP())',
            [
                'organization_id' => $data['organization_id'],
                'user_id' => $data['user_id'],
                'subject_email' => $data['subject_email'],
                'consent_version_id' => $data['consent_version_id'],
                'ip' => $data['ip'],
                'user_agent' => $data['user_agent'],
            ]
        );

        return (int) $this->pdo->lastInsertId();
    }
}

