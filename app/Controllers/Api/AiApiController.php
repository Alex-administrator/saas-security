<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\AiService;
use App\Support\Request;
use App\Support\Validator;

final class AiApiController extends BaseController
{
    public function analyzeText(Request $request)
    {
        $payload = Validator::validate($request->all(), [
            'text' => 'required|string|min:16|max:12000',
        ]);

        return $this->jsonSuccess([
            'result' => (new AiService())->analyzeText(
                (string) $payload['text'],
                $this->organizationId(),
                $this->userId()
            ),
        ]);
    }
}

