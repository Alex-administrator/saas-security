<?php
declare(strict_types=1);

namespace App\Repositories;

final class EventRepository extends BaseRepository
{
    public function listByOrganization(int $organizationId): array
    {
        return $this->fetchAll(
            'SELECT e.*, u.name AS creator_name
             FROM events e
             INNER JOIN users u ON u.id = e.created_by
             WHERE e.organization_id = :organization_id
             ORDER BY e.starts_at_utc ASC',
            ['organization_id' => $organizationId]
        );
    }

    public function upcomingPublic(int $limit = 6): array
    {
        return $this->fetchAll(
            'SELECT e.*, o.name AS organization_name
             FROM events e
             INNER JOIN organizations o ON o.id = e.organization_id
             WHERE e.starts_at_utc >= UTC_TIMESTAMP()
             ORDER BY e.starts_at_utc ASC
             LIMIT ' . (int) $limit
        );
    }

    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO events (organization_id, created_by, title, description, location, starts_at_utc, ends_at_utc, created_at, updated_at)
             VALUES (:organization_id, :created_by, :title, :description, :location, :starts_at_utc, :ends_at_utc, UTC_TIMESTAMP(), UTC_TIMESTAMP())',
            [
                'organization_id' => $data['organization_id'],
                'created_by' => $data['created_by'],
                'title' => $data['title'],
                'description' => $data['description'],
                'location' => $data['location'],
                'starts_at_utc' => $data['starts_at_utc'],
                'ends_at_utc' => $data['ends_at_utc'],
            ]
        );

        return (int) $this->pdo->lastInsertId();
    }
}

