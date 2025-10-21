<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Support\Helpers;
use App\Support\Session;

final class AdminMiddleware
{
    public function __invoke(Request $request, callable $next)
    {
        $user = Session::get('user');
        if (!$user || ($user['role'] ?? 'user') !== 'admin') {
            if (str_starts_with($request->path(), '/api/')) {
                Helpers::json([
                    'success' => false,
                    'error_code' => 'forbidden',
                    'message' => 'Bu alana erişimin yok.',
                ], 403);
            }
            Helpers::abort(403, 'Bu alana erişim iznin yok.');
        }

        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        return $next();
    }
}
