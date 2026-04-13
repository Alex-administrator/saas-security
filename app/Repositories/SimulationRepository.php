<?php
declare(strict_types=1);

namespace App\Repositories;

final class SimulationRepository extends BaseRepository
{
    public function listByOrganization(int $organizationId): array
    {
        return $this->fetchAll(
            'SELECT sp.*, u.name AS creator_name
             FROM simulation_programs sp
             INNER JOIN users u ON u.id = sp.created_by
             WHERE sp.organization_id = :organization_id
             ORDER BY sp.created_at DESC',
            ['organization_id' => $organizationId]
        );
    }

    public function createProgram(array $data): int
    {
        $this->execute(
            'INSERT INTO simulation_programs (organization_id, created_by, title, description, template_name, status, launched_at, created_at, updated_at)
             VALUES (:organization_id, :created_by, :title, :description, :template_name, :status, NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP())',
            [
                'organization_id' => $data['organization_id'],
                'created_by' => $data['created_by'],
                'title' => $data['title'],
                'description' => $data['description'],
                'template_name' => $data['template_name'],
                'status' => $data['status'],
            ]
        );

        return (int) $this->pdo->lastInsertId();
    }

    public function findProgramByIdAndOrganization(int $id, int $organizationId): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM simulation_programs WHERE id = :id AND organization_id = :organization_id LIMIT 1',
            ['id' => $id, 'organization_id' => $organizationId]
        );
    }

    public function addTarget(array $data): int
    {
        $this->execute(
            'INSERT INTO simulation_targets (simulation_program_id, email, full_name, status, token, created_at, updated_at)
             VALUES (:simulation_program_id, :email, :full_name, :status, :token, UTC_TIMESTAMP(), UTC_TIMESTAMP())',
            [
                'simulation_program_id' => $data['simulation_program_id'],
                'email' => $data['email'],
                'full_name' => $data['full_name'],
                'status' => $data['status'],
                'token' => $data['token'],
            ]
        );

        return (int) $this->pdo->lastInsertId();
    }

    public function targetsByProgram(int $programId): array
    {
        return $this->fetchAll(
            'SELECT * FROM simulation_targets WHERE simulation_program_id = :simulation_program_id ORDER BY id ASC',
            ['simulation_program_id' => $programId]
        );
    }

    public function markProgramLaunched(int $programId): void
    {
        $this->execute(
            'UPDATE simulation_programs SET status = :status, launched_at = UTC_TIMESTAMP(), updated_at = UTC_TIMESTAMP() WHERE id = :id',
            ['status' => 'launched', 'id' => $programId]
        );
    }

    public function findTargetByToken(string $token): ?array
    {
        return $this->fetchOne(
            'SELECT st.*, sp.title AS program_title, sp.description AS program_description, sp.template_name
             FROM simulation_targets st
             INNER JOIN simulation_programs sp ON sp.id = st.simulation_program_id
             WHERE st.token = :token
             LIMIT 1',
            ['token' => $token]
        );
    }

    public function markOpened(int $targetId): void
    {
        $this->execute(
            'UPDATE simulation_targets SET status = :status, opened_at = COALESCE(opened_at, UTC_TIMESTAMP()), updated_at = UTC_TIMESTAMP() WHERE id = :id',
            ['status' => 'opened', 'id' => $targetId]
        );
    }

    public function markReported(int $targetId): void
    {
        $this->execute(
            'UPDATE simulation_targets SET status = :status, reported_at = COALESCE(reported_at, UTC_TIMESTAMP()), updated_at = UTC_TIMESTAMP() WHERE id = :id',
            ['status' => 'reported', 'id' => $targetId]
        );
    }

    public function markCompleted(int $targetId): void
    {
        $this->execute(
            'UPDATE simulation_targets SET status = :status, completed_at = COALESCE(completed_at, UTC_TIMESTAMP()), updated_at = UTC_TIMESTAMP() WHERE id = :id',
            ['status' => 'completed', 'id' => $targetId]
        );
    }

    public function addEvent(int $programId, int $targetId, string $type, array $meta = []): void
    {
        $this->execute(
            'INSERT INTO simulation_events (simulation_program_id, simulation_target_id, type, meta_json, created_at)
             VALUES (:simulation_program_id, :simulation_target_id, :type, :meta_json, UTC_TIMESTAMP())',
            [
                'simulation_program_id' => $programId,
                'simulation_target_id' => $targetId,
                'type' => $type,
                'meta_json' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]
        );
    }

    public function report(int $programId, int $organizationId): ?array
    {
        $program = $this->fetchOne(
            'SELECT * FROM simulation_programs WHERE id = :id AND organization_id = :organization_id LIMIT 1',
            ['id' => $programId, 'organization_id' => $organizationId]
        );

        if ($program === null) {
            return null;
        }

        $stats = $this->fetchOne(
            'SELECT
                COUNT(*) AS total_targets,
                SUM(CASE WHEN opened_at IS NOT NULL THEN 1 ELSE 0 END) AS opened_count,
                SUM(CASE WHEN reported_at IS NOT NULL THEN 1 ELSE 0 END) AS reported_count,
                SUM(CASE WHEN completed_at IS NOT NULL THEN 1 ELSE 0 END) AS completed_count
             FROM simulation_targets
             WHERE simulation_program_id = :simulation_program_id',
            ['simulation_program_id' => $programId]
        ) ?? [];

        return array_merge($program, $stats);
    }
}

