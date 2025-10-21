<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Config\AppConfig;
use App\Http\Request;
use App\Support\Helpers;
use App\Support\Session;

final class MaintenanceMiddleware
{
    public function __invoke(Request $request, callable $next)
    {
        if (AppConfig::bool('MAINTENANCE_MODE')) {
            $user = Session::get('user');
            if (!$user || ($user['role'] ?? 'user') !== 'admin') {
                http_response_code(503);
                header('Retry-After: 300');
                echo Helpers::render('maintenance', []);
                return false;
            }
        }

        return $next();
    }
}
