<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\ToolService;
use App\Support\Auth;
use App\Support\Request;
use App\Support\Validator;

final class ToolApiController extends BaseController
{
    public function passwordStrength(Request $request)
    {
        $payload = Validator::validate($request->all(), ['password' => 'required|string|min:8']);

        return $this->jsonSuccess([
            'result' => (new ToolService())->passwordStrength(
                (string) $payload['password'],
                Auth::organizationId(),
                Auth::userId()
            ),
        ]);
    }

    public function urlAnalyze(Request $request)
    {
        $payload = Validator::validate($request->all(), ['url' => 'required|url']);

        return $this->jsonSuccess([
            'result' => (new ToolService())->urlAnalyze(
                (string) $payload['url'],
                Auth::organizationId(),
                Auth::userId()
            ),
        ]);
    }
}

