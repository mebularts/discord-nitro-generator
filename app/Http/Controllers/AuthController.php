<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Config\AppConfig;
use App\Http\Middleware\CsrfMiddleware;
use App\Http\Request;
use App\Security\RateLimiter;
use App\Services\AuthService;
use App\Support\Helpers;
use App\Support\Session;
use InvalidArgumentException;
use RuntimeException;

final class AuthController extends Controller
{
    private AuthService $auth;

    public function __construct()
    {
        $this->auth = new AuthService();
    }

    public function showRegister(): void
    {
        $this->view('auth/register', ['title' => 'Kayıt Ol']);
    }

    public function register(Request $request): void
    {
        $this->validateCsrf($request);
        $data = $request->all();
        try {
            $user = $this->auth->register((string) $data['email'], (string) $data['username'], (string) $data['password']);
            Helpers::redirect('/');
        } catch (InvalidArgumentException $exception) {
            $this->view('auth/register', ['title' => 'Kayıt Ol', 'error' => $exception->getMessage(), 'old' => $data]);
        } catch (RuntimeException $exception) {
            $this->view('auth/register', ['title' => 'Kayıt Ol', 'error' => $exception->getMessage(), 'old' => $data]);
        }
    }

    public function showLogin(): void
    {
        $this->view('auth/login', ['title' => 'Giriş Yap']);
    }

    public function login(Request $request): void
    {
        $this->validateCsrf($request);
        $identifier = (string) $request->input('identifier');
        $password = (string) $request->input('password');
        $key = 'login_' . ($_SERVER['REMOTE_ADDR'] ?? 'cli');
        $limit = (int) AppConfig::get('RATE_LIMIT_LOGIN_PER_MIN', '5');
        if (!RateLimiter::hit($key, $limit, 60)) {
            $this->view('auth/login', ['title' => 'Giriş Yap', 'error' => 'Çok fazla deneme. Lütfen daha sonra tekrar dene.']);
            return;
        }

        try {
            $this->auth->login($identifier, $password);
            Helpers::redirect('/');
        } catch (RuntimeException $exception) {
            $this->view('auth/login', ['title' => 'Giriş Yap', 'error' => $exception->getMessage()]);
        }
    }

    public function logout(): void
    {
        $this->auth->logout();
        Helpers::redirect('/');
    }
}
