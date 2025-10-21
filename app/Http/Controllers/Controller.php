<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Security\CsrfTokenManager;
use App\Support\Helpers;
use App\Support\Session;
use App\Http\Request;

abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        $data['csrf_token'] = CsrfTokenManager::token();
        $data['session'] = $_SESSION ?? [];
        $content = Helpers::render($view, $data);
        echo Helpers::render('layouts/main', array_merge($data, ['content' => $content]));
    }

    protected function json(array $data, int $status = 200): void
    {
        Helpers::json($data, $status);
    }

    protected function redirect(string $location): void
    {
        Helpers::redirect($location);
    }

    protected function validateCsrf(Request $request): void
    {
        if (!Helpers::validateCsrf($request->csrfToken())) {
            Helpers::json([
                'success' => false,
                'error_code' => 'invalid_csrf',
                'message' => 'Oturum doğrulaması başarısız.',
            ], 400);
        }
    }

    protected function user(): ?array
    {
        return Session::get('user');
    }

    protected function requireAuth(): array
    {
        $user = $this->user();
        if (!$user) {
            Helpers::redirect('/login');
        }
        return $user;
    }
}
