<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Support\Auth;
use App\Support\Response;

abstract class BaseController
{
    protected function view(string $view, array $data = [], int $status = 200): Response
    {
        return Response::view($view, $data, $status);
    }

    protected function redirect(string $path): Response
    {
        return Response::redirect($path);
    }

    protected function jsonSuccess(array $data = [], int $status = 200): Response
    {
        return Response::apiSuccess($data, $status);
    }

    protected function jsonError(string $code, string $message, int $status = 400, array $details = []): Response
    {
        return Response::apiError($code, $message, $status, $details);
    }

    protected function organizationId(): int
    {
        return (int) Auth::organizationId();
    }

    protected function userId(): int
    {
        return (int) Auth::userId();
    }
}

