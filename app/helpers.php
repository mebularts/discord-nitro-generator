<?php

declare(strict_types=1);

use App\I18n\Translator;
use App\Models\Setting;

/**
 * Get environment variable with optional default.
 */
function env(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}

/**
 * Resolve locale ensuring translation files exist.
 */
function resolve_locale(?string $requested): string
{
    $available = available_locales();
    $requested = $requested ? strtolower($requested) : null;

    if ($requested && in_array($requested, $available, true)) {
        return $requested;
    }

    $fallback = $available[0] ?? 'en';
    $configured = setting('default_locale', env('DEFAULT_LOCALE', $fallback));
    if (in_array($configured, $available, true)) {
        return $configured;
    }

    return $fallback;
}

/**
 * Return cached translator instance.
 */
function translator(): Translator
{
    static $translator;
    if ($translator instanceof Translator) {
        return $translator;
    }

    $translator = new Translator(
        cache_path('translations'),
        env('DEEPL_AUTH_KEY'),
        setting('default_locale', env('DEFAULT_LOCALE', 'en'))
    );

    return $translator;
}

/**
 * Translate helper.
 */
function __(string $key, array $replace = [], ?string $locale = null): string
{
    return translator()->get($key, $replace, $locale ?? APP_LOCALE ?? env('DEFAULT_LOCALE', 'en'));
}

/**
 * Return list of locales supported by language files.
 */
function available_locales(): array
{
    static $locales;
    if ($locales !== null) {
        return $locales;
    }

    $locales = [];
    foreach (glob(APP_PATH . '/lang/*.php') as $file) {
        $locales[] = basename($file, '.php');
    }

    sort($locales);
    return $locales;
}

/**
 * Get value from settings table or default.
 */
function setting(string $key, ?string $default = null): ?string
{
    static $cache = [];
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    try {
        $value = Setting::value($key);
    } catch (Throwable $e) {
        $value = $default;
    }

    $cache[$key] = $value ?? $default;
    return $cache[$key];
}

/**
 * Shared view data store.
 */
function view_share(array $data): void
{
    static $shared = [];
    $shared = array_merge($shared, $data);
    $GLOBALS['__view_shared'] = $shared;
}

function view_shared(): array
{
    return $GLOBALS['__view_shared'] ?? [];
}

function view(string $template, array $data = []): string
{
    $path = APP_PATH . '/Views/' . $template . '.php';
    if (!is_file($path)) {
        throw new RuntimeException('View not found: ' . $template);
    }

    $shared = view_shared();
    $data = array_merge($shared, $data);
    extract($data, EXTR_SKIP);

    ob_start();
    include $path;
    return (string) ob_get_clean();
}

function respond(string $content, int $status = 200, array $headers = []): void
{
    http_response_code($status);
    foreach ($headers as $header => $value) {
        header($header . ': ' . $value, true, $status);
    }

    echo $content;
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function abort(int $status, string $message = ''): void
{
    http_response_code($status);
    $template = $status === 404 ? 'errors/404' : 'errors/error';
    $content = view($template, [
        'code'    => $status,
        'message' => $message ?: __('errors.generic'),
    ]);
    echo $content;
    exit;
}

function storage_path(string $path = ''): string
{
    return BASE_PATH . '/storage' . ($path ? '/' . ltrim($path, '/') : '');
}

function cache_path(string $path = ''): string
{
    return storage_path('cache' . ($path ? '/' . ltrim($path, '/') : ''));
}

function log_path(string $path = ''): string
{
    return storage_path('logs' . ($path ? '/' . ltrim($path, '/') : ''));
}

function log_error(string $message): void
{
    $logFile = log_path('app.log');
    if (!is_dir(dirname($logFile))) {
        mkdir(dirname($logFile), 0775, true);
    }

    error_log('[' . date('c') . '] ' . $message . PHP_EOL, 3, $logFile);
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(16));
    }

    return $_SESSION['_csrf_token'];
}

function csrf_input(): string
{
    return '<input type="hidden" name="_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf_token(): void
{
    $token = $_POST['_token'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        abort(419, 'CSRF token mismatch.');
    }
}

function old(string $key, ?string $default = null): ?string
{
    $value = $_SESSION['__old'][$key] ?? $default;
    return is_string($value) ? $value : $default;
}

function with_old(array $inputs): void
{
    $_SESSION['__old'] = $inputs;
}

function flash(string $key, ?string $message = null)
{
    if ($message === null) {
        $value = $_SESSION['__flash'][$key] ?? null;
        unset($_SESSION['__flash'][$key]);
        return $value;
    }

    $_SESSION['__flash'][$key] = $message;
}

function asset(string $path): string
{
    return '/assets/' . ltrim($path, '/');
}

function route(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

function current_url(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri    = $_SERVER['REQUEST_URI'] ?? '/';

    return $scheme . '://' . $host . $uri;
}

function app_icon_url(string $size = '256x256'): string
{
    if (strpos($size, 'x') !== false) {
        [$width] = explode('x', $size, 2);
        $numeric = (int) $width;
        $size = $numeric >= 512 ? '512' : ($numeric >= 192 ? '192' : '192');
    }

    return '/icon.php?size=' . urlencode($size);
}

function json_response(array $payload, int $status = 200): void
{
    header('Content-Type: application/json', true, $status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function request_ip(): string
{
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $parts = explode(',', (string) $_SERVER[$key]);
            return trim($parts[0]);
        }
    }

    return '0.0.0.0';
}

function request_user_agent(): string
{
    return $_SERVER['HTTP_USER_AGENT'] ?? 'cli';
}

function voter_hash(): string
{
    return hash('sha256', request_ip() . '|' . request_user_agent());
}
