<?php
declare(strict_types=1);

namespace App\Requests;

use App\Support\Request;
use App\Support\Validator;

final class EventRequest
{
    public function validate(Request $request): array
    {
        return Validator::validate($request->all(), [
            'title' => 'required|string|min:4|max:160',
            'description' => 'required|string|min:12|max:1200',
            'location' => 'string|max:160',
            'starts_at_utc' => 'required|date',
            'ends_at_utc' => 'required|date',
        ]);
    }
}

