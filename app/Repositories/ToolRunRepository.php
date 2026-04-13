<?php
declare(strict_types=1);

namespace App\Repositories;

final class ToolRunRepository extends BaseRepository
{
    public function log(array $data): int
    {
        $this->execute(
            'INSERT INTO tool_runs (organization_id, user_id, tool_name, input_value, result_json, created_at)
             VALUES (:organization_id, :user_id, :tool_name, :input_value, :result_json, UTC_TIMESTAMP())',
            [
                'organization_id' => $data['organization_id'],
                'user_id' => $data['user_id'],
                'tool_name' => $data['tool_name'],
                'input_value' => $data['input_value'],
                'result_json' => json_encode($data['result'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]
        );

        return (int) $this->pdo->lastInsertId();
    }
}

