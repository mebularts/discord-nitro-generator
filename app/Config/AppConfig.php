<?php

declare(strict_types=1);

namespace App\Config;

use RuntimeException;

final class AppConfig
{
    private const DEFAULTS = [
        'APP_ENV' => 'prod',
        'DB_DSN' => 'mysql:host=localhost;dbname=solveclone;charset=utf8mb4',
        'DB_USER' => 'root',
        'DB_PASS' => '',
        'MAX_AVATAR_SIZE_MB' => '2',
        'ALLOWED_SOCIALS' => 'instagram.com,twitter.com,x.com,youtube.com,tiktok.com,github.com,linkedin.com',
        'RATE_LIMIT_LOGIN_PER_MIN' => '5',
        'MAINTENANCE_MODE' => '0',
    ];

    private static array $config = [];

    public static function bootstrap(): void
    {
        $env = self::loadDotEnv();
        self::$config = array_merge(self::DEFAULTS, $env, getenv() ?: []);

        if (!in_array(self::$config['APP_ENV'], ['dev', 'prod'], true)) {
            throw new RuntimeException('Invalid APP_ENV value.');
        }
    }

    private static function loadDotEnv(): array
    {
        $envPath = BASE_PATH . '/.env';
        if (!is_file($envPath)) {
            return [];
        }
        $values = [];
        foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            $values[$key] = $value;
        }
        return $values;
    }

    public static function get(string $key, ?string $default = null): string
    {
        return self::$config[$key] ?? $default ?? '';
    }

    public static function bool(string $key): bool
    {
        return filter_var(self::get($key, '0'), FILTER_VALIDATE_BOOL);
    }

    public static function int(string $key, int $default = 0): int
    {
        return (int) (self::$config[$key] ?? $default);
    }

    public static function array(string $key): array
    {
        $value = self::$config[$key] ?? '';
        if ($value === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', strtolower($value)))));
    }
}
