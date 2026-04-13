<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Requests\LoginRequest;
use App\Services\AuthService;
use App\Support\Auth;
use App\Support\Request;
use App\Support\Session;
use RuntimeException;

final class AuthController extends BaseController
{
    public function showLogin(Request $request)
    {
        return $this->view('auth/login', ['pageTitle' => 'Вход']);
    }

    public function login(Request $request)
    {
        $payload = (new LoginRequest())->validate($request);

        try {
            $result = (new AuthService())->attempt((string) $payload['email'], (string) $payload['password']);
            Auth::login($result['user'], $result['organization'], $result['membership'], $result['subscription']);
            Session::flash('message', 'Вход выполнен успешно.');
            return $this->redirect('/dashboard');
        } catch (RuntimeException $exception) {
            Session::flash('message', $exception->getMessage());
            Session::flashInput($request->except(['password', '_token']));
            return $this->redirect('/login');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        Session::flush();
        Session::regenerate();
        return $this->redirect('/login');
    }
}

