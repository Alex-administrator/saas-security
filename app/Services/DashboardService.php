<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\ArticleRepository;
use App\Repositories\EventRepository;
use App\Repositories\OrganizationRepository;
use App\Repositories\SimulationRepository;

final class DashboardService
{
    public function overview(int $organizationId): array
    {
        $organizationRepository = new OrganizationRepository();
        $articleRepository = new ArticleRepository();
        $eventRepository = new EventRepository();
        $simulationRepository = new SimulationRepository();

        return [
            'metrics' => $organizationRepository->dashboardMetrics($organizationId),
            'recent_articles' => $articleRepository->recentByOrganization($organizationId),
            'upcoming_events' => array_slice($eventRepository->listByOrganization($organizationId), 0, 5),
            'simulations' => array_slice($simulationRepository->listByOrganization($organizationId), 0, 5),
        ];
    }
}

