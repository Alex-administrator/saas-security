<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Support\Auth;
use App\Support\Request;
use App\Support\Response;

final class GuestMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (Auth::check()) {
            return Response::redirect('/dashboard');
        }

        return $next($request);
    }
}

