<?php
declare(strict_types=1);

namespace App\Requests;

use App\Support\Request;
use App\Support\Validator;

final class ArticleRequest
{
    public function validate(Request $request): array
    {
        return Validator::validate($request->all(), [
            'title' => 'required|string|min:8|max:160',
            'excerpt' => 'required|string|min:16|max:320',
            'content' => 'required|string|min:50',
            'cover_image' => 'url',
        ]);
    }
}

