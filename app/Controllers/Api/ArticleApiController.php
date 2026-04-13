<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Repositories\ArticleRepository;
use App\Support\Request;

final class ArticleApiController extends BaseController
{
    public function search(Request $request)
    {
        $term = trim((string) $request->query('q', ''));
        return $this->jsonSuccess([
            'items' => $term === '' ? [] : (new ArticleRepository())->searchPublic($term),
        ]);
    }
}

