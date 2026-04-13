<?php
declare(strict_types=1);

namespace App\Support;

final class MigrationRunner
{
    public function run(): void
    {
        $pdo = Database::connection();
        $pdo->exec('CREATE TABLE IF NOT EXISTS migrations (id INT AUTO_INCREMENT PRIMARY KEY, migration VARCHAR(255) NOT NULL UNIQUE, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)');

        $applied = $pdo->query('SELECT migration FROM migrations')->fetchAll(\PDO::FETCH_COLUMN) ?: [];
        $files = glob(base_path('database/migrations/*.php')) ?: [];
        sort($files);

        foreach ($files as $file) {
            $migration = basename($file);
            if (in_array($migration, $applied, true)) {
                continue;
            }

            $statements = require $file;
            foreach ($statements as $statement) {
                $pdo->exec($statement);
            }

            $insert = $pdo->prepare('INSERT INTO migrations (migration) VALUES (:migration)');
            $insert->execute(['migration' => $migration]);
        }
    }
}

