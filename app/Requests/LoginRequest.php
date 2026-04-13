<?php
declare(strict_types=1);

namespace App\Requests;

use App\Support\Request;
use App\Support\Validator;

final class LoginRequest
{
    public function validate(Request $request): array
    {
        return Validator::validate($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8|max:1024',
        ]);
    }
}

