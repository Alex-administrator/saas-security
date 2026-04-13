<?php
declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'SaaS Security'),
    'env' => env('APP_ENV', 'production'),
    'debug' => filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOL),
    'url' => env('APP_URL', 'http://localhost:8080'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'locale' => env('APP_LOCALE', 'ru'),
    'key' => env('APP_KEY', ''),
];

