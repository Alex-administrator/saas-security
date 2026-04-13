<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\AuditLogRepository;
use App\Repositories\JobRepository;
use App\Repositories\OrganizationRepository;
use App\Repositories\SimulationRepository;
use App\Support\Logger;
use RuntimeException;

final class SimulationService
{
    public function createProgram(int $organizationId, int $userId, array $data): int
    {
        $organization = (new OrganizationRepository())->findById($organizationId);
        if ($organization === null) {
            throw new RuntimeException('Организация не найдена.');
        }

        $targets = $this->parseTargets((string) $data['targets'], (string) $organization['allowed_domains']);
        if ($targets === []) {
            throw new RuntimeException('Нужно указать хотя бы одного адресата.');
        }

        $repository = new SimulationRepository();
        $programId = $repository->createProgram([
            'organization_id' => $organizationId,
            'created_by' => $userId,
            'title' => trim((string) $data['title']),
            'description' => trim((string) $data['description']),
            'template_name' => trim((string) $data['template_name']),
            'status' => 'draft',
        ]);

        foreach ($targets as $target) {
            $repository->addTarget([
                'simulation_program_id' => $programId,
                'email' => $target['email'],
                'full_name' => $target['full_name'],
                'status' => 'pending',
                'token' => bin2hex(random_bytes(24)),
            ]);
        }

        (new AuditLogRepository())->log([
            'organization_id' => $organizationId,
            'user_id' => $userId,
            'action' => 'simulation.created',
            'entity_type' => 'simulation_program',
            'entity_id' => $programId,
            'context' => ['title' => $data['title'], 'targets' => count($targets)],
        ]);

        return $programId;
    }

    public function launch(int $programId, int $organizationId, int $userId): void
    {
        $repository = new SimulationRepository();
        $program = $repository->findProgramByIdAndOrganization($programId, $organizationId);

        if ($program === null) {
            throw new RuntimeException('Сценарий не найден.');
        }

        $targets = $repository->targetsByProgram($programId);
        foreach ($targets as $target) {
            (new JobRepository())->enqueue('simulation_launch', [
                'program_id' => $programId,
                'target_id' => $target['id'],
                'template_name' => $program['template_name'],
            ]);
        }

        $repository->markProgramLaunched($programId);

        (new AuditLogRepository())->log([
            'organization_id' => $organizationId,
            'user_id' => $userId,
            'action' => 'simulation.launched',
            'entity_type' => 'simulation_program',
            'entity_id' => $programId,
            'context' => ['targets' => count($targets)],
        ]);
    }

    public function handleLaunchPayload(array $payload): void
    {
        Logger::info('Simulation notification queued', [
            'program_id' => $payload['program_id'] ?? null,
            'target_id' => $payload['target_id'] ?? null,
            'template_name' => $payload['template_name'] ?? null,
        ]);
    }

    public function findTarget(string $token): ?array
    {
        return (new SimulationRepository())->findTargetByToken($token);
    }

    public function openTarget(string $token): bool
    {
        $repository = new SimulationRepository();
        $target = $repository->findTargetByToken($token);
        if ($target === null) {
            return false;
        }

        if (($target['opened_at'] ?? null) === null && ($target['status'] ?? 'pending') === 'pending') {
            $repository->markOpened((int) $target['id']);
            $repository->addEvent((int) $target['simulation_program_id'], (int) $target['id'], 'opened');
        }

        return true;
    }

    public function reportTarget(string $token): void
    {
        $repository = new SimulationRepository();
        $target = $repository->findTargetByToken($token);
        if ($target === null) {
            throw new RuntimeException('Токен симуляции не найден.');
        }

        if (($target['reported_at'] ?? null) === null && ($target['status'] ?? '') !== 'completed') {
            $repository->markReported((int) $target['id']);
            $repository->addEvent((int) $target['simulation_program_id'], (int) $target['id'], 'reported');
        }
    }

    public function completeTarget(string $token): void
    {
        $repository = new SimulationRepository();
        $target = $repository->findTargetByToken($token);
        if ($target === null) {
            throw new RuntimeException('Токен симуляции не найден.');
        }

        if (($target['completed_at'] ?? null) === null) {
            $repository->markCompleted((int) $target['id']);
            $repository->addEvent((int) $target['simulation_program_id'], (int) $target['id'], 'completed');
        }
    }

    public function report(int $programId, int $organizationId): ?array
    {
        return (new SimulationRepository())->report($programId, $organizationId);
    }

    private function parseTargets(string $input, string $allowedDomains): array
    {
        $fallback = env('SIMULATION_ALLOWED_FALLBACK_DOMAIN', 'example.com') ?? 'example.com';
        $domains = array_filter(array_map(static fn(string $domain): string => mb_strtolower(trim($domain)), explode(',', $allowedDomains !== '' ? $allowedDomains : $fallback)));
        $lines = preg_split('/\r\n|\r|\n/', $input) ?: [];
        $targets = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            [$email, $fullName] = array_pad(array_map('trim', explode(',', $line, 2)), 2, '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Список адресатов содержит некорректный email: ' . $email);
            }

            $domain = mb_strtolower(substr(strrchr($email, '@') ?: '', 1));
            if ($domain === '' || !in_array($domain, $domains, true)) {
                throw new RuntimeException('Email ' . $email . ' не входит в разрешенный домен организации.');
            }

            $targets[] = [
                'email' => mb_strtolower($email),
                'full_name' => $fullName !== '' ? $fullName : $email,
            ];
        }

        return $targets;
    }
}
