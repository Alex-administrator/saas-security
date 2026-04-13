<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Support\Auth;
use App\Support\Request;
use App\Support\Response;

final class RbacMiddleware
{
    public function handle(Request $request, callable $next, string ...$roles): Response
    {
        if (!Auth::hasAnyRole($roles)) {
            return $request->expectsJson()
                ? Response::apiError('FORBIDDEN', 'Insufficient role privileges', 403)
                : Response::view('dashboard/error', [
                    'message' => 'Недостаточно прав для выполнения этого действия.',
                    'status' => 403,
                ], 403);
        }

        return $next($request);
    }
}

