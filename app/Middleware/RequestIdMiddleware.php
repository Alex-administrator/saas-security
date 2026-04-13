<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Support\Request;
use App\Support\RequestContext;
use App\Support\Response;

final class RequestIdMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        RequestContext::set('request_id', 'req_' . bin2hex(random_bytes(8)));
        $response = $next($request);
        return $response->setHeader('X-Request-Id', request_id());
    }
}

