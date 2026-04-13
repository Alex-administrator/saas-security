<?php
declare(strict_types=1);

return [
    <<<SQL
    CREATE TABLE IF NOT EXISTS organizations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(160) NOT NULL,
        slug VARCHAR(160) NOT NULL UNIQUE,
        allowed_domains TEXT NOT NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    SQL,
    <<<SQL
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(160) NOT NULL,
        email VARCHAR(190) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        mfa_enabled TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    SQL,
    <<<SQL
    CREATE TABLE IF NOT EXISTS organization_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        organization_id INT NOT NULL,
        user_id INT NOT NULL,
        role VARCHAR(50) NOT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        last_seen_at DATETIME NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_org_user (organization_id, user_id),
        CONSTRAINT fk_org_users_org FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
        CONSTRAINT fk_org_users_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    SQL,
    <<<SQL
    CREATE TABLE IF NOT EXISTS plans (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        code VARCHAR(80) NOT NULL UNIQUE,
        article_limit INT NOT NULL DEFAULT 100,
        event_limit INT NOT NULL DEFAULT 100,
        simulation_limit INT NOT NULL DEFAULT 100,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    SQL,
    <<<SQL
    CREATE TABLE IF NOT EXISTS subscriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        organization_id INT NOT NULL,
        plan_id INT NOT NULL,
        status VARCHAR(30) NOT NULL,
        starts_at DATETIME NOT NULL,
        ends_at DATETIME NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_subscriptions_org FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
        CONSTRAINT fk_subscriptions_plan FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    SQL,
    <<<SQL
    CREATE TABLE IF NOT EXISTS articles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        organization_id INT NOT NULL,
        author_id INT NOT NULL,
        title VARCHAR(190) NOT NULL,
        slug VARCHAR(190) NOT NULL UNIQUE,
        excerpt VARCHAR(320) NOT NULL,
        content LONGTEXT NOT NULL,
        status VARCHAR(30) NOT NULL,
        cover_image VARCHAR(500) NULL,
        published_at DATETIME NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_articles_org (organization_id),
        INDEX idx_articles_status (status),
        CONSTRAINT fk_articles_org FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
        CONSTRAINT fk_articles_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    SQL,
    <<<SQL
    CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        organization_id INT NOT NULL,
        created_by INT NOT NULL,
        title VARCHAR(190) NOT NULL,
        description TEXT NOT NULL,
        location VARCHAR(190) NULL,
        starts_at_utc DATETIME NOT NULL,
        ends_at_utc DATETIME NOT NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_events_org (organization_id),
        INDEX idx_events_start (starts_at_utc),
        CONSTRAINT fk_events_org FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
        CONSTRAINT fk_events_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    SQL,
    <<<SQL
    CREATE TABLE IF NOT EXISTS consent_versions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(80) NOT NULL,
        version VARCHAR(50) NOT NULL,
        title VARCHAR(190) NOT NULL,
        content LONGTEXT NOT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    SQL,
    <<<SQL
    CREATE TABLE IF NOT EXISTS consents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        organization_id INT NULL,
        user_id INT NULL,
        subject_email VARCHAR(190) NULL,
        consent_version_id INT NOT NULL,
        ip VARCHAR(64) NOT NULL,
        user_agent TEXT NOT NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_consents_org (organization_id),
        CONSTRAINT fk_consents_org FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL,
        CONSTRAINT fk_consents_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        CONSTRAINT fk_consents_version FOREIGN KEY (consent_version_id) REFERENCES consent_versions(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    SQL,
    <<<SQL
    CREATE TABLE IF NOT EXISTS audit_logs (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        organization_id INT NULL,
        user_id INT NULL,
        action VARCHAR(160) NOT NULL,
        entity_type VARCHAR(80) NOT NULL,
        entity_id BIGINT NOT NULL,
        context_json JSON NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_audit_org (organization_id),
        INDEX idx_audit_action (action),
        CONSTRAINT fk_audit_org FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL,
        CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    SQL,
    <<<SQL
    CREATE TABLE IF NOT EXISTS jobs (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(120) NOT NULL,
        payload JSON NOT NULL,
        status VARCHAR(30) NOT NULL,
        available_at DATETIME NOT NULL,
        attempts INT NOT NULL DEFAULT 0,
        max_attempts INT NOT NULL DEFAULT 5,
        last_error TEXT NULL,
        processed_at DATETIME NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_jobs_status (status),
        INDEX idx_jobs_available (available_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    SQL,
    <<<SQL
    CREATE TABLE IF NOT EXISTS job_attempts (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        job_id BIGINT NOT NULL,
        status VARCHAR(30) NOT NULL,
        error_message TEXT NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_job_attempts_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    SQL,
    <<<SQL
    CREATE TABLE IF NOT EXISTS failed_jobs (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        job_id BIGINT NOT NULL,
        type VARCHAR(120) NOT NULL,
        payload JSON NOT NULL,
        error_message TEXT NOT NULL,
        failed_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_failed_jobs_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    SQL,
    <<<SQL
    CREATE TABLE IF NOT EXISTS simulation_programs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        organization_id INT NOT NULL,
        created_by INT NOT NULL,
        title VARCHAR(190) NOT NULL,
        description TEXT NOT NULL,
        template_name VARCHAR(80) NOT NULL,
        status VARCHAR(30) NOT NULL,
        launched_at DATETIME NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_simulation_programs_org (organization_id),
        CONSTRAINT fk_simulation_programs_org FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
        CONSTRAINT fk_simulation_programs_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    SQL,
    <<<SQL
    CREATE TABLE IF NOT EXISTS simulation_targets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        simulation_program_id INT NOT NULL,
        email VARCHAR(190) NOT NULL,
        full_name VARCHAR(190) NOT NULL,
        status VARCHAR(30) NOT NULL,
        token VARCHAR(64) NOT NULL UNIQUE,
        opened_at DATETIME NULL,
        reported_at DATETIME NULL,
        completed_at DATETIME NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_simulation_targets_program (simulation_program_id),
        CONSTRAINT fk_simulation_targets_program FOREIGN KEY (simulation_program_id) REFERENCES simulation_programs(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    SQL,
    <<<SQL
    CREATE TABLE IF NOT EXISTS simulation_events (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        simulation_program_id INT NOT NULL,
        simulation_target_id INT NOT NULL,
        type VARCHAR(80) NOT NULL,
        meta_json JSON NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_simulation_events_program (simulation_program_id),
        CONSTRAINT fk_simulation_events_program FOREIGN KEY (simulation_program_id) REFERENCES simulation_programs(id) ON DELETE CASCADE,
        CONSTRAINT fk_simulation_events_target FOREIGN KEY (simulation_target_id) REFERENCES simulation_targets(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    SQL,
    <<<SQL
    CREATE TABLE IF NOT EXISTS ai_requests (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        organization_id INT NULL,
        user_id INT NULL,
        mode VARCHAR(50) NOT NULL,
        input_hash CHAR(64) NOT NULL,
        input_excerpt VARCHAR(255) NOT NULL,
        result_json JSON NOT NULL,
        status VARCHAR(30) NOT NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_ai_requests_org FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL,
        CONSTRAINT fk_ai_requests_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    SQL,
    <<<SQL
    CREATE TABLE IF NOT EXISTS tool_runs (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        organization_id INT NULL,
        user_id INT NULL,
        tool_name VARCHAR(80) NOT NULL,
        input_value TEXT NOT NULL,
        result_json JSON NOT NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_tool_runs_org FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL,
        CONSTRAINT fk_tool_runs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    SQL,
    <<<SQL
    CREATE TABLE IF NOT EXISTS api_tokens (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        organization_id INT NOT NULL,
        name VARCHAR(120) NOT NULL,
        token_hash CHAR(64) NOT NULL UNIQUE,
        last_used_at DATETIME NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_api_tokens_org FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    SQL,
];

