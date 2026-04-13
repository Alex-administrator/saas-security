<?php
declare(strict_types=1);

use App\Support\Auth;
use App\Support\Config;
use App\Support\Csrf;
use App\Support\Env;
use App\Support\RequestContext;
use App\Support\Session;

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $base = dirname(__DIR__);
        return $path === '' ? $base : $base . DIRECTORY_SEPARATOR . ltrim($path, '\\/');
    }
}

if (!function_exists('config_path')) {
    function config_path(string $path = ''): string
    {
        return base_path('config' . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '\\/') : ''));
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return base_path('storage' . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '\\/') : ''));
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        return base_path('public' . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '\\/') : ''));
    }
}

if (!function_exists('env')) {
    function env(string $key, ?string $default = null): ?string
    {
        return Env::get($key, $default);
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $baseUrl = rtrim((string) config('app.url', ''), '/');
        return $path === '' ? $baseUrl : $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return Session::old($key, $default);
    }
}

if (!function_exists('errors')) {
    function errors(): array
    {
        return Session::errors();
    }
}

if (!function_exists('error_for')) {
    function error_for(string $key): ?string
    {
        $errors = errors();
        return $errors[$key][0] ?? null;
    }
}

if (!function_exists('flash')) {
    function flash(string $key, mixed $default = null): mixed
    {
        return Session::flashValue($key, $default);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
    }
}

if (!function_exists('auth_user')) {
    function auth_user(): ?array
    {
        return Auth::user();
    }
}

if (!function_exists('current_organization')) {
    function current_organization(): ?array
    {
        return Auth::organization();
    }
}

if (!function_exists('current_role')) {
    function current_role(): ?string
    {
        return Auth::role();
    }
}

if (!function_exists('request_id')) {
    function request_id(): string
    {
        return (string) RequestContext::get('request_id', 'req_unknown');
    }
}

