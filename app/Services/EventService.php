<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\AuditLogRepository;
use App\Repositories\EventRepository;

final class EventService
{
    public function create(int $organizationId, int $userId, array $data): int
    {
        $eventId = (new EventRepository())->create([
            'organization_id' => $organizationId,
            'created_by' => $userId,
            'title' => trim((string) $data['title']),
            'description' => trim((string) $data['description']),
            'location' => trim((string) ($data['location'] ?? '')),
            'starts_at_utc' => gmdate('Y-m-d H:i:s', strtotime((string) $data['starts_at_utc'])),
            'ends_at_utc' => gmdate('Y-m-d H:i:s', strtotime((string) $data['ends_at_utc'])),
        ]);

        (new AuditLogRepository())->log([
            'organization_id' => $organizationId,
            'user_id' => $userId,
            'action' => 'event.created',
            'entity_type' => 'event',
            'entity_id' => $eventId,
            'context' => ['title' => $data['title']],
        ]);

        return $eventId;
    }
}

