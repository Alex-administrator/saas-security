<?php
declare(strict_types=1);

namespace App\Repositories;

final class SubscriptionRepository extends BaseRepository
{
    public function findCurrentForOrganization(int $organizationId): ?array
    {
        return $this->fetchOne(
            'SELECT s.*, p.name AS plan_name, p.code AS plan_code, p.article_limit, p.event_limit, p.simulation_limit
             FROM subscriptions s
             INNER JOIN plans p ON p.id = s.plan_id
             WHERE s.organization_id = :organization_id
             ORDER BY s.id DESC
             LIMIT 1',
            ['organization_id' => $organizationId]
        );
    }
}

