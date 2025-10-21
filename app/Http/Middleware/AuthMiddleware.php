<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Support\Helpers;
use App\Support\Session;

final class AuthMiddleware
{
    public function __invoke(Request $request, callable $next)
    {
        if (!Session::get('user')) {
            if (str_starts_with($request->path(), '/api/')) {
                Helpers::json([
                    'success' => false,
                    'error_code' => 'unauthenticated',
                    'message' => 'Oturum açman gerekiyor.',
                ], 401);
            }
            Helpers::redirect('/login');
        }

        return $next();
    }
}
