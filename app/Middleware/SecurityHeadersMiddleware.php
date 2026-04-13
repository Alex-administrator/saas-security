<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Support\Request;
use App\Support\Response;

final class SecurityHeadersMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        $response = $next($request);

        foreach ((array) config('security.headers', []) as $name => $value) {
            $response->setHeader((string) $name, (string) $value);
        }

        return $response;
    }
}

