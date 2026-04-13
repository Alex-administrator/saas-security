<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap/app.php';

use App\Support\JobWorker;
use App\Support\MaintenanceService;
use App\Support\MigrationRunner;
use App\Support\SeederRunner;

$command = $argv[1] ?? 'help';

switch ($command) {
    case 'migrate':
        (new MigrationRunner())->run();
        echo "Migrations completed.\n";
        break;

    case 'seed':
        (new SeederRunner())->run();
        echo "Database seeded.\n";
        break;

    case 'queue:work':
        $once = in_array('--once', $argv, true);
        $worker = new JobWorker();

        if ($once) {
            $processed = $worker->runOnce();
            echo $processed ? "Processed one job.\n" : "No jobs available.\n";
            break;
        }

        while (true) {
            $processed = $worker->runOnce();
            if (!$processed) {
                sleep((int) config('queue.sleep_seconds', 5));
            }
        }

    case 'schedule:run':
        (new MaintenanceService())->runScheduledTasks();
        echo "Scheduled tasks executed.\n";
        break;

    case 'help':
    default:
        echo "Available commands:\n";
        echo "  php console.php migrate\n";
        echo "  php console.php seed\n";
        echo "  php console.php queue:work [--once]\n";
        echo "  php console.php schedule:run\n";
        break;
}

