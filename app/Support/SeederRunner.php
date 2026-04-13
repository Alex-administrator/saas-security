<?php
declare(strict_types=1);

namespace App\Support;

final class SeederRunner
{
    public function run(): void
    {
        $seederFile = base_path('database/seeders/DatabaseSeeder.php');
        require_once $seederFile;

        $class = 'Database\\Seeders\\DatabaseSeeder';
        $seeder = new $class();
        $seeder->run();
    }
}

