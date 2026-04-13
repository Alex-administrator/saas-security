<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Support\Database;
use App\Support\Request;

final class HealthController extends BaseController
{
    public function health(Request $request)
    {
        return $this->jsonSuccess([
            'status' => 'ok',
            'app' => config('app.name'),
        ]);
    }

    public function ready(Request $request)
    {
        if (!Database::available()) {
            return $this->jsonError('DB_UNAVAILABLE', 'Database is unavailable', 503);
        }

        return $this->jsonSuccess(['status' => 'ready']);
    }
}
