<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Support\Csrf;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;

final class CsrfMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        $token = (string) ($request->input('_token') ?? $request->header('X-CSRF-TOKEN', ''));
        if (!Csrf::validate($token)) {
            if ($request->expectsJson()) {
                return Response::apiError('CSRF_TOKEN_INVALID', 'Invalid CSRF token', 419);
            }

            Session::flash('message', 'Сессия истекла. Повторите действие.');
            return Response::redirect($request->backUrl('/'));
        }

        return $next($request);
    }
}

