<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Support\RateLimiter;
use App\Support\Request;
use App\Support\Response;

final class RateLimitMiddleware
{
    public function handle(Request $request, callable $next, string $profile = 'api'): Response
    {
        $config = config('security.rate_limit.' . $profile, ['attempts' => 60, 'window' => 60]);
        $limiter = new RateLimiter();
        $key = implode(':', [$profile, $request->path(), $request->ip()]);
        $result = $limiter->hit($key, (int) $config['attempts'], (int) $config['window']);

        if (!$result['allowed']) {
            $retryAfter = max($result['reset_at'] - time(), 1);
            if ($request->expectsJson()) {
                return Response::apiError('RATE_LIMITED', 'Too many requests', 429, [
                    'retry_after' => $retryAfter,
                ]);
            }

            return Response::view('dashboard/error', [
                'message' => 'Слишком много запросов. Повторите через ' . $retryAfter . ' сек.',
                'status' => 429,
            ], 429);
        }

        $response = $next($request);
        $response->setHeader('X-RateLimit-Remaining', (string) $result['remaining']);
        return $response;
    }
}
