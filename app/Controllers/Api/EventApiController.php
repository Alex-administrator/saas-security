<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Repositories\EventRepository;
use App\Support\Request;

final class EventApiController extends BaseController
{
    public function index(Request $request)
    {
        return $this->jsonSuccess([
            'items' => (new EventRepository())->upcomingPublic(),
        ]);
    }
}

