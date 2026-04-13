<?php
declare(strict_types=1);

namespace App\Support;

final class MaintenanceService
{
    public function runScheduledTasks(): void
    {
        $worker = new JobWorker();
        for ($index = 0; $index < 10; $index++) {
            if (!$worker->runOnce()) {
                break;
            }
        }

        if (!Database::available()) {
            return;
        }

        $pdo = Database::connection();
        $statement = $pdo->prepare('DELETE FROM jobs WHERE status = :status AND processed_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 7 DAY)');
        $statement->execute(['status' => 'completed']);
    }
}
