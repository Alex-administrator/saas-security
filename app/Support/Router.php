<?php
declare(strict_types=1);

namespace App\Support;

use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\RequestIdMiddleware;
use App\Middleware\RbacMiddleware;
use App\Middleware\SecurityHeadersMiddleware;
use App\Middleware\SubscriptionMiddleware;
use App\Middleware\TenantMiddleware;

final class Router
{
    private array $routes = [];
    private array $middlewareAliases = [
        'auth' => AuthMiddleware::class,
        'csrf' => CsrfMiddleware::class,
        'guest' => GuestMiddleware::class,
        'rate_limit' => RateLimitMiddleware::class,
        'request_id' => RequestIdMiddleware::class,
        'rbac' => RbacMiddleware::class,
        'security_headers' => SecurityHeadersMiddleware::class,
        'subscription' => SubscriptionMiddleware::class,
        'tenant' => TenantMiddleware::class,
    ];

    public function get(string $path, array $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, array $handler, array $middleware): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
            'middleware' => array_merge(['request_id', 'security_headers'], $middleware),
        ];
    }

    public function dispatch(Request $request): Response
    {
        $route = $this->match($request);

        if ($route === null) {
            return $request->expectsJson()
                ? Response::apiError('NOT_FOUND', 'Route not found', 404)
                : Response::view('dashboard/error', ['message' => 'Страница не найдена.', 'status' => 404], 404);
        }

        $request->setRouteParams($route['params']);

        $handler = function (Request $request) use ($route): Response {
            [$controllerClass, $method] = $route['handler'];
            $controller = new $controllerClass();
            return $controller->{$method}($request);
        };

        $pipeline = array_reduce(
            array_reverse($route['middleware']),
            function (callable $next, string $middlewareSpec): callable {
                return function (Request $request) use ($next, $middlewareSpec): Response {
                    [$alias, $rawParameters] = array_pad(explode(':', $middlewareSpec, 2), 2, '');
                    $parameters = $rawParameters === '' ? [] : array_map('trim', explode(',', $rawParameters));
                    $middlewareClass = $this->middlewareAliases[$alias] ?? null;

                    if ($middlewareClass === null) {
                        throw new \RuntimeException('Middleware not registered: ' . $alias);
                    }

                    $middleware = new $middlewareClass();
                    return $middleware->handle($request, $next, ...$parameters);
                };
            },
            $handler
        );

        return $pipeline($request);
    }

    private function match(Request $request): ?array
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }

            $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (!preg_match($pattern, $request->path(), $matches)) {
                continue;
            }

            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }

            return [
                'handler' => $route['handler'],
                'middleware' => $route['middleware'],
                'params' => $params,
            ];
        }

        return null;
    }
}

