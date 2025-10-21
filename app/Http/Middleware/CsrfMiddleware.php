<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Support\Helpers;

final class CsrfMiddleware
{
    public function __invoke(Request $request, callable $next)
    {
        if ($request->method() === 'POST') {
            if (!Helpers::validateCsrf($request->csrfToken())) {
                Helpers::json([
                    'success' => false,
                    'error_code' => 'invalid_csrf',
                    'message' => 'Güvenlik doğrulaması başarısız.',
                ], 400);
            }
        }

        return $next();
    }
}
