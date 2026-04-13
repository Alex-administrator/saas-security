<?php
declare(strict_types=1);

namespace App\Repositories;

final class AiRequestRepository extends BaseRepository
{
    public function log(array $data): int
    {
        $this->execute(
            'INSERT INTO ai_requests (organization_id, user_id, mode, input_hash, input_excerpt, result_json, status, created_at)
             VALUES (:organization_id, :user_id, :mode, :input_hash, :input_excerpt, :result_json, :status, UTC_TIMESTAMP())',
            [
                'organization_id' => $data['organization_id'],
                'user_id' => $data['user_id'],
                'mode' => $data['mode'],
                'input_hash' => $data['input_hash'],
                'input_excerpt' => $data['input_excerpt'],
                'result_json' => json_encode($data['result'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'status' => $data['status'],
            ]
        );

        return (int) $this->pdo->lastInsertId();
    }
}

