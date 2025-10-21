<?php

declare(strict_types=1);

namespace App\Support;

use App\Config\AppConfig;
use Throwable;

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) === 0;
    }
}

if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}

final class Helpers
{
    public static function basePath(string $path = ''): string
    {
        return rtrim(BASE_PATH . '/' . ltrim($path, '/'), '/');
    }

    public static function viewPath(string $view): string
    {
        return self::basePath('app/Views/' . $view . '.php');
    }

    public static function asset(string $path): string
    {
        return '/' . ltrim($path, '/');
    }

    public static function redirect(string $location): void
    {
        header('Location: ' . $location);
        exit;
    }

    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_THROW_ON_ERROR);
        exit;
    }

    public static function validateCsrf(string $token): bool
    {
        return hash_equals(Session::get('csrf_token', ''), $token);
    }

    public static function escape(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function now(): string
    {
        return (new \DateTimeImmutable('now'))->format('Y-m-d H:i:s');
    }

    public static function render(string $view, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require self::viewPath($view);
        return (string) ob_get_clean();
    }

    public static function abort(int $code, string $message = 'Error'): void
    {
        http_response_code($code);
        echo self::render('errors/' . $code, ['message' => $message]);
        exit;
    }

    public static function maintenanceModeEnabled(): bool
    {
        return AppConfig::bool('MAINTENANCE_MODE');
    }

    public static function registerErrorHandler(): void
    {
        set_exception_handler(static function (Throwable $throwable): void {
            error_log('[exception] ' . $throwable->getMessage() . ' @ ' . $throwable->getFile() . ':' . $throwable->getLine());
            if (AppConfig::get('APP_ENV') === 'dev') {
                http_response_code(500);
                echo '<pre>' . self::escape((string) $throwable) . '</pre>';
            } else {
                self::abort(500, 'Beklenmeyen bir hata oluştu.');
            }
        });
    }
}
