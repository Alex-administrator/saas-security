<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Support\Database;
use RuntimeException;

final class DatabaseSeeder
{
    public function run(): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        $pdo->exec("
            INSERT INTO plans (name, code, article_limit, event_limit, simulation_limit)
            VALUES ('Starter', 'starter', 100, 50, 20)
            ON DUPLICATE KEY UPDATE name = VALUES(name)
        ");

        $pdo->exec("
            INSERT INTO organizations (name, slug, allowed_domains)
            VALUES ('Acme Security', 'acme-security', 'example.com')
            ON DUPLICATE KEY UPDATE name = VALUES(name), allowed_domains = VALUES(allowed_domains)
        ");

        $adminEmail = env('SEED_ADMIN_EMAIL', 'admin@example.com') ?? 'admin@example.com';
        $adminPassword = env('SEED_ADMIN_PASSWORD');

        if (($adminPassword === null || $adminPassword === '') && config('app.env', 'production') === 'production') {
            throw new RuntimeException('SEED_ADMIN_PASSWORD must be set before running seed in production.');
        }

        $adminPassword ??= 'ChangeMe123!';

        $passwordAlgorithm = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
        $passwordHash = password_hash($adminPassword, $passwordAlgorithm);
        $statement = $pdo->prepare("
            INSERT INTO users (name, email, password_hash, mfa_enabled)
            VALUES (:name, :email, :password_hash, 0)
            ON DUPLICATE KEY UPDATE name = VALUES(name), password_hash = VALUES(password_hash)
        ");
        $statement->execute([
            'name' => 'Platform Admin',
            'email' => $adminEmail,
            'password_hash' => $passwordHash,
        ]);

        $organizationId = (int) $pdo->query("SELECT id FROM organizations WHERE slug = 'acme-security'")->fetchColumn();
        $lookup = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $lookup->execute(['email' => $adminEmail]);
        $userId = (int) $lookup->fetchColumn();
        $planId = (int) $pdo->query("SELECT id FROM plans WHERE code = 'starter'")->fetchColumn();

        $statement = $pdo->prepare("
            INSERT INTO organization_users (organization_id, user_id, role, is_active)
            VALUES (:organization_id, :user_id, 'org_admin', 1)
            ON DUPLICATE KEY UPDATE role = VALUES(role), is_active = VALUES(is_active)
        ");
        $statement->execute([
            'organization_id' => $organizationId,
            'user_id' => $userId,
        ]);

        $statement = $pdo->prepare("
            INSERT INTO subscriptions (organization_id, plan_id, status, starts_at, ends_at)
            SELECT :organization_id, :plan_id, 'active', UTC_TIMESTAMP(), DATE_ADD(UTC_TIMESTAMP(), INTERVAL 1 YEAR)
            WHERE NOT EXISTS (
                SELECT 1 FROM subscriptions WHERE organization_id = :organization_id_check AND status = 'active'
            )
        ");
        $statement->execute([
            'organization_id' => $organizationId,
            'plan_id' => $planId,
            'organization_id_check' => $organizationId,
        ]);

        $statement = $pdo->prepare("
            INSERT INTO consent_versions (type, version, title, content, is_active)
            SELECT 'privacy', '1.0', 'Privacy Notice', 'Training telemetry is stored for awareness and reporting only.', 1
            WHERE NOT EXISTS (
                SELECT 1 FROM consent_versions WHERE type = 'privacy' AND version = '1.0'
            )
        ");
        $statement->execute();

        $statement = $pdo->prepare("
            INSERT INTO articles (organization_id, author_id, title, slug, excerpt, content, status, published_at)
            VALUES (
                :organization_id,
                :author_id,
                'Welcome to Security Awareness',
                'welcome-security-awareness',
                'Краткий обзор того, как использовать платформу после переноса на новый сервер.',
                'После деплоя на новый сервер проверьте .env, выполните migrate и seed, затем откройте dashboard и health endpoint.',
                'published',
                UTC_TIMESTAMP()
            )
            ON DUPLICATE KEY UPDATE title = VALUES(title), excerpt = VALUES(excerpt), content = VALUES(content)
        ");
        $statement->execute([
            'organization_id' => $organizationId,
            'author_id' => $userId,
        ]);

        $statement = $pdo->prepare("
            INSERT INTO events (organization_id, created_by, title, description, location, starts_at_utc, ends_at_utc)
            SELECT
                :organization_id,
                :created_by,
                'Quarterly Security Review',
                'Регулярная встреча по проверке awareness-метрик и operational health платформы.',
                'Online',
                DATE_ADD(UTC_TIMESTAMP(), INTERVAL 7 DAY),
                DATE_ADD(DATE_ADD(UTC_TIMESTAMP(), INTERVAL 7 DAY), INTERVAL 1 HOUR)
            WHERE NOT EXISTS (
                SELECT 1 FROM events WHERE organization_id = :organization_id_check AND title = 'Quarterly Security Review'
            )
        ");
        $statement->execute([
            'organization_id' => $organizationId,
            'created_by' => $userId,
            'organization_id_check' => $organizationId,
        ]);

        $pdo->commit();
    }
}
