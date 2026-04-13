<?php
declare(strict_types=1);

namespace App\Repositories;

final class AuditLogRepository extends BaseRepository
{
    public function log(array $data): void
    {
        $this->execute(
            'INSERT INTO audit_logs (organization_id, user_id, action, entity_type, entity_id, context_json, created_at)
             VALUES (:organization_id, :user_id, :action, :entity_type, :entity_id, :context_json, UTC_TIMESTAMP())',
            [
                'organization_id' => $data['organization_id'],
                'user_id' => $data['user_id'],
                'action' => $data['action'],
                'entity_type' => $data['entity_type'],
                'entity_id' => $data['entity_id'],
                'context_json' => json_encode($data['context'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]
        );
    }
}

