<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Support\Auth;
use App\Support\Request;
use App\Support\Response;

final class SubscriptionMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        $subscription = Auth::subscription();
        if ($subscription === null || !in_array($subscription['status'], ['active', 'grace'], true)) {
            return $request->expectsJson()
                ? Response::apiError('SUBSCRIPTION_REQUIRED', 'Active subscription is required', 402)
                : Response::view('dashboard/error', [
                    'message' => 'Для этого раздела нужна активная подписка.',
                    'status' => 402,
                ], 402);
        }

        return $next($request);
    }
}
