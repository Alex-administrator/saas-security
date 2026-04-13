<?php
declare(strict_types=1);

namespace App\Support;

use App\Repositories\JobRepository;
use App\Services\NotificationService;
use App\Services\SimulationService;
use PDOException;
use Throwable;

final class JobWorker
{
    public function runOnce(): bool
    {
        $jobs = new JobRepository();

        try {
            $job = $jobs->fetchAvailable();
        } catch (PDOException $exception) {
            Logger::warning('Queue storage is not ready yet', [
                'message' => $exception->getMessage(),
            ]);
            return false;
        }

        if ($job === null) {
            return false;
        }

        try {
            $jobs->markProcessing((int) $job['id']);
            $payload = json_decode((string) $job['payload'], true) ?: [];

            switch ($job['type']) {
                case 'article_published_notification':
                    (new NotificationService())->handleArticlePublished($payload);
                    break;

                case 'simulation_launch':
                    (new SimulationService())->handleLaunchPayload($payload);
                    break;

                default:
                    Logger::warning('Unknown job type skipped', ['type' => $job['type']]);
                    break;
            }

            $jobs->markCompleted((int) $job['id']);
        } catch (Throwable $exception) {
            $jobs->markFailed((int) $job['id'], $exception->getMessage());
            Logger::error('Queue job failed', [
                'job_id' => $job['id'],
                'type' => $job['type'],
                'message' => $exception->getMessage(),
            ]);
        }

        return true;
    }
}
