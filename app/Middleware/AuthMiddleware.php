<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Support\Auth;
use App\Support\Request;
use App\Support\Response;

final class AuthMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (!Auth::check()) {
            return $request->expectsJson()
                ? Response::apiError('UNAUTHORIZED', 'Authentication required', 401)
                : Response::redirect('/login');
        }

        return $next($request);
    }
}

