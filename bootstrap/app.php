<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    $baseDirectory = base_path('app');

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDirectory . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});

App\Support\Env::load(base_path('.env'));
App\Support\Config::load(config_path());
date_default_timezone_set((string) config('app.timezone', 'UTC'));

App\Support\Session::start((bool) config('security.session.secure_cookie', false), (string) config('security.session.same_site', 'Lax'));
App\Support\Database::configure(config('db'));
App\Support\Auth::boot();

$router = new App\Support\Router();

require base_path('routes/web.php');
require base_path('routes/api.php');

return $router;

