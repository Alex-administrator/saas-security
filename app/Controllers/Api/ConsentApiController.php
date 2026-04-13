<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\ConsentService;
use App\Support\Auth;
use App\Support\Request;
use App\Support\Validator;

final class ConsentApiController extends BaseController
{
    public function store(Request $request)
    {
        $payload = Validator::validate($request->all(), [
            'subject_email' => 'email',
            'type' => 'string',
        ]);

        $consentId = (new ConsentService())->record([
            'organization_id' => Auth::organizationId(),
            'user_id' => Auth::userId(),
            'subject_email' => $payload['subject_email'] ?? null,
            'type' => $payload['type'] ?? 'privacy',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $this->jsonSuccess(['consent_id' => $consentId], 201);
    }
}

