<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Config\AppConfig;
use App\Http\Controllers\Controller;
use App\Http\Request;
use App\Security\RateLimiter;
use App\Services\AuthService;
use App\Support\Helpers;
use InvalidArgumentException;
use RuntimeException;

final class AuthController extends Controller
{
    private AuthService $auth;

    public function __construct()
    {
        $this->auth = new AuthService();
    }

    public function register(Request $request): void
    {
        $this->validateCsrf($request);
        try {
            $user = $this->auth->register(
                (string) $request->input('email'),
                (string) $request->input('username'),
                (string) $request->input('password')
            );
            unset($user['password_hash']);
            $this->json(['success' => true, 'user' => $user]);
        } catch (InvalidArgumentException|RuntimeException $exception) {
            $this->json([
                'success' => false,
                'error_code' => 'validation_error',
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function login(Request $request): void
    {
        $this->validateCsrf($request);
        $key = 'login_' . ($_SERVER['REMOTE_ADDR'] ?? 'cli');
        $limit = (int) AppConfig::get('RATE_LIMIT_LOGIN_PER_MIN', '5');
        if (!RateLimiter::hit($key, $limit, 60)) {
            $this->json([
                'success' => false,
                'error_code' => 'rate_limited',
                'message' => 'Çok fazla deneme yaptın. Bir süre bekle.',
            ], 429);
            return;
        }

        try {
            $user = $this->auth->login(
                (string) $request->input('identifier'),
                (string) $request->input('password')
            );
            unset($user['password_hash']);
            $this->json(['success' => true, 'user' => $user]);
        } catch (RuntimeException $exception) {
            $this->json([
                'success' => false,
                'error_code' => 'invalid_credentials',
                'message' => $exception->getMessage(),
            ], 401);
        }
    }

    public function logout(): void
    {
        $this->auth->logout();
        $this->json(['success' => true]);
    }
}
