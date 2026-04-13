<?php
declare(strict_types=1);

namespace App\Repositories;

final class OrganizationRepository extends BaseRepository
{
    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM organizations WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->fetchOne('SELECT * FROM organizations WHERE slug = :slug LIMIT 1', ['slug' => $slug]);
    }

    public function membershipsForUser(int $userId): array
    {
        return $this->fetchAll(
            'SELECT ou.*, o.name AS organization_name, o.slug, o.allowed_domains
             FROM organization_users ou
             INNER JOIN organizations o ON o.id = ou.organization_id
             WHERE ou.user_id = :user_id AND ou.is_active = 1
             ORDER BY ou.id ASC',
            ['user_id' => $userId]
        );
    }

    public function findMembership(int $userId, int $organizationId): ?array
    {
        return $this->fetchOne(
            'SELECT ou.*, o.name AS organization_name, o.slug, o.allowed_domains
             FROM organization_users ou
             INNER JOIN organizations o ON o.id = ou.organization_id
             WHERE ou.user_id = :user_id AND ou.organization_id = :organization_id AND ou.is_active = 1
             LIMIT 1',
            [
                'user_id' => $userId,
                'organization_id' => $organizationId,
            ]
        );
    }

    public function dashboardMetrics(int $organizationId): array
    {
        return [
            'articles' => (int) ($this->fetchOne('SELECT COUNT(*) AS aggregate FROM articles WHERE organization_id = :organization_id', ['organization_id' => $organizationId])['aggregate'] ?? 0),
            'events' => (int) ($this->fetchOne('SELECT COUNT(*) AS aggregate FROM events WHERE organization_id = :organization_id', ['organization_id' => $organizationId])['aggregate'] ?? 0),
            'simulations' => (int) ($this->fetchOne('SELECT COUNT(*) AS aggregate FROM simulation_programs WHERE organization_id = :organization_id', ['organization_id' => $organizationId])['aggregate'] ?? 0),
        ];
    }
}

