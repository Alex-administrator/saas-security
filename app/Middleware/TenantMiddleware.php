<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Support\Auth;
use App\Support\Request;
use App\Support\Response;

final class TenantMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (Auth::organization() === null) {
            return $request->expectsJson()
                ? Response::apiError('TENANT_REQUIRED', 'Active organization is required', 403)
                : Response::redirect('/dashboard');
        }

        return $next($request);
    }
}

