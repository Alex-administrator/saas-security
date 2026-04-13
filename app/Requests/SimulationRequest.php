<?php
declare(strict_types=1);

namespace App\Requests;

use App\Support\Request;
use App\Support\Validator;

final class SimulationRequest
{
    public function validate(Request $request): array
    {
        return Validator::validate($request->all(), [
            'title' => 'required|string|min:6|max:160',
            'description' => 'required|string|min:16|max:1000',
            'template_name' => 'required|string|in:credential_check,invoice_review,document_share',
            'targets' => 'required|string|min:6',
        ]);
    }
}

