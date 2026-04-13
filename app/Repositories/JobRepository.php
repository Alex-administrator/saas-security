<?php
declare(strict_types=1);

namespace App\Repositories;

final class JobRepository extends BaseRepository
{
    public function enqueue(string $type, array $payload, int $maxAttempts = 5): int
    {
        $this->execute(
            'INSERT INTO jobs (type, payload, status, available_at, attempts, max_attempts, created_at, updated_at)
             VALUES (:type, :payload, :status, UTC_TIMESTAMP(), 0, :max_attempts, UTC_TIMESTAMP(), UTC_TIMESTAMP())',
            [
                'type' => $type,
                'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'status' => 'pending',
                'max_attempts' => $maxAttempts,
            ]
        );

        return (int) $this->pdo->lastInsertId();
    }

    public function fetchAvailable(): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM jobs
             WHERE status IN ("pending", "retrying")
               AND available_at <= UTC_TIMESTAMP()
               AND attempts < max_attempts
             ORDER BY id ASC
             LIMIT 1'
        );
    }

    public function markProcessing(int $jobId): void
    {
        $this->execute(
            'UPDATE jobs SET status = :status, attempts = attempts + 1, updated_at = UTC_TIMESTAMP() WHERE id = :id',
            ['status' => 'processing', 'id' => $jobId]
        );
    }

    public function markCompleted(int $jobId): void
    {
        $this->execute(
            'UPDATE jobs SET status = :status, processed_at = UTC_TIMESTAMP(), updated_at = UTC_TIMESTAMP() WHERE id = :id',
            ['status' => 'completed', 'id' => $jobId]
        );
    }

    public function markFailed(int $jobId, string $message): void
    {
        $job = $this->fetchOne('SELECT * FROM jobs WHERE id = :id LIMIT 1', ['id' => $jobId]);
        if ($job === null) {
            return;
        }

        $status = ((int) $job['attempts'] >= (int) $job['max_attempts']) ? 'failed' : 'retrying';
        $this->execute(
            'UPDATE jobs
             SET status = :status,
                 last_error = :last_error,
                 available_at = DATE_ADD(UTC_TIMESTAMP(), INTERVAL 1 MINUTE),
                 updated_at = UTC_TIMESTAMP()
             WHERE id = :id',
            [
                'status' => $status,
                'last_error' => $message,
                'id' => $jobId,
            ]
        );

        $this->execute(
            'INSERT INTO job_attempts (job_id, status, error_message, created_at)
             VALUES (:job_id, :status, :error_message, UTC_TIMESTAMP())',
            [
                'job_id' => $jobId,
                'status' => $status,
                'error_message' => $message,
            ]
        );

        if ($status === 'failed') {
            $this->execute(
                'INSERT INTO failed_jobs (job_id, type, payload, error_message, failed_at)
                 VALUES (:job_id, :type, :payload, :error_message, UTC_TIMESTAMP())',
                [
                    'job_id' => $jobId,
                    'type' => $job['type'],
                    'payload' => $job['payload'],
                    'error_message' => $message,
                ]
            );
        }
    }
}

