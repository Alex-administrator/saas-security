<?php
declare(strict_types=1);

$router = require __DIR__ . '/../bootstrap/app.php';

$checks = [
    'router_booted' => $router instanceof App\Support\Router,
    'app_name_present' => is_string(config('app.name')) && config('app.name') !== '',
    'db_config_present' => is_string(config('db.host')) && config('db.host') !== '',
    'public_entry_exists' => is_file(base_path('public/index.php')),
    'docker_compose_exists' => is_file(base_path('docker-compose.yml')),
    'migration_exists' => is_file(base_path('database/migrations/001_create_core_tables.php')),
];

$failed = array_keys(array_filter($checks, static fn(bool $passed): bool => !$passed));

if ($failed !== []) {
    fwrite(STDERR, "Smoke test failed:\n - " . implode("\n - ", $failed) . "\n");
    exit(1);
}

fwrite(STDOUT, "Smoke test passed.\n");

