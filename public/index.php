<?php
declare(strict_types=1);

use App\Support\AuthorizationException;
use App\Support\Logger;
use App\Support\Request;
use App\Support\Response;
use App\Support\ValidationException;

$router = require dirname(__DIR__) . '/bootstrap/app.php';
$request = Request::capture();

try {
    $response = $router->dispatch($request);
} catch (ValidationException $exception) {
    if ($request->expectsJson()) {
        $response = Response::apiError(
            'VALIDATION_ERROR',
            'Validation failed',
            422,
            $exception->errors()
        );
    } else {
        App\Support\Session::flash('errors', $exception->errors());
        App\Support\Session::flashInput($request->except(['password', 'password_confirmation', '_token']));
        $response = Response::redirect($request->backUrl('/'));
    }
} catch (AuthorizationException $exception) {
    $response = $request->expectsJson()
        ? Response::apiError('FORBIDDEN', $exception->getMessage(), 403)
        : Response::redirect('/login');
} catch (Throwable $exception) {
    Logger::error('Unhandled exception', [
        'request_id' => request_id(),
        'message' => $exception->getMessage(),
        'trace' => config('app.debug') ? $exception->getTraceAsString() : null,
    ]);

    if ($request->expectsJson()) {
        $response = Response::apiError(
            'SERVER_ERROR',
            config('app.debug') ? $exception->getMessage() : 'Internal server error',
            500
        );
    } else {
        $response = Response::view('dashboard/error', [
            'message' => config('app.debug') ? $exception->getMessage() : 'Произошла ошибка. Проверьте логи приложения.',
            'status' => 500,
        ], 500);
    }
}

$response->send();

