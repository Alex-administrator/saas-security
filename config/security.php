<?php
declare(strict_types=1);

return [
    'session' => [
        'secure_cookie' => filter_var(env('SESSION_SECURE_COOKIE', 'false'), FILTER_VALIDATE_BOOL),
        'same_site' => env('SESSION_SAME_SITE', 'Lax'),
    ],
    'headers' => [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
        'Content-Security-Policy' => "default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'self';",
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
    ],
    'rate_limit' => [
        'login' => ['attempts' => 5, 'window' => 900],
        'api' => ['attempts' => 60, 'window' => 60],
        'simulation' => ['attempts' => 30, 'window' => 300],
    ],
];

