<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\SimulationService;
use App\Support\Request;

final class SimulationApiController extends BaseController
{
    public function report(Request $request)
    {
        $report = (new SimulationService())->report((int) $request->route('id'), $this->organizationId());
        if ($report === null) {
            return $this->jsonError('NOT_FOUND', 'Simulation report not found', 404);
        }

        return $this->jsonSuccess(['report' => $report]);
    }
}
