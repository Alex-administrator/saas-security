<?php
declare(strict_types=1);

namespace App\Support;

final class Session
{
    private static bool $started = false;
    private static array $flashBag = [];

    public static function start(bool $secureCookie, string $sameSite): void
    {
        if (self::$started) {
            return;
        }

        if (PHP_SAPI === 'cli') {
            if (!isset($_SESSION) || !is_array($_SESSION)) {
                $_SESSION = [];
            }
            self::$flashBag = [];
            self::$started = true;
            return;
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secureCookie || self::requestIsSecure(),
            'httponly' => true,
            'samesite' => $sameSite,
        ]);

        session_start();
        self::$flashBag = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        self::$started = true;
    }

    private static function requestIsSecure(): bool
    {
        $https = strtolower((string) ($_SERVER['HTTPS'] ?? ''));
        $forwardedProto = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
        $forwardedSsl = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_SSL'] ?? ''));
        $scheme = strtolower((string) ($_SERVER['REQUEST_SCHEME'] ?? ''));

        return in_array($https, ['on', '1'], true)
            || $forwardedProto === 'https'
            || $forwardedSsl === 'on'
            || $scheme === 'https';
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, $_SESSION);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flush(): void
    {
        $_SESSION = [];
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function flashInput(array $input): void
    {
        self::flash('old_input', $input);
    }

    public static function old(string $key, mixed $default = null): mixed
    {
        return self::$flashBag['old_input'][$key] ?? $default;
    }

    public static function errors(): array
    {
        return self::$flashBag['errors'] ?? [];
    }

    public static function flashValue(string $key, mixed $default = null): mixed
    {
        return self::$flashBag[$key] ?? $default;
    }
}
