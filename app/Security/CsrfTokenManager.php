<?php

declare(strict_types=1);

namespace App\Security;

use App\Support\Session;

final class CsrfTokenManager
{
    public static function ensureToken(): void
    {
        if (!Session::get('csrf_token')) {
            Session::put('csrf_token', bin2hex(random_bytes(32)));
        }
    }

    public static function token(): string
    {
        return (string) Session::get('csrf_token', '');
    }
}
