<?php

declare(strict_types=1);

namespace App\Security;

use App\Support\Session;

final class RateLimiter
{
    public static function hit(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        $now = time();
        $attempts = Session::get('rate_limit_' . $key, []);
        $attempts = array_filter($attempts, static fn (int $timestamp) => ($now - $timestamp) < $decaySeconds);
        $attempts[] = $now;
        Session::put('rate_limit_' . $key, $attempts);
        return count($attempts) <= $maxAttempts;
    }
}
