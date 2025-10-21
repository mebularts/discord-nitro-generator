<?php
declare(strict_types=1);

use App\I18n\Translator;

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function app_path(string $path = ''): string
{
    return __DIR__ . ($path ? '/' . ltrim($path, '/') : '');
}

function storage_path(string $path = ''): string
{
    return dirname(__DIR__) . '/storage' . ($path ? '/' . ltrim($path, '/') : '');
}

function setting(string $key, $default = '')
{
    return App\Models\Setting::get($key, $default);
}

function locales_enabled(): array
{
    $raw = setting('i18n.enabled_locales', getenv('ENABLED_LOCALES') ?: '["en","tr"]');
    if (is_string($raw)) {
        $decoded = json_decode($raw, true);
        $locales = is_array($decoded) ? $decoded : [$raw];
    } elseif (is_array($raw)) {
        $locales = $raw;
    } else {
        $locales = ['en'];
    }

    $locales = array_values(array_unique(array_map(static fn ($l) => strtolower(trim((string) $l)), $locales)));
    $default = strtolower(setting('i18n.default_locale', getenv('DEFAULT_LOCALE') ?: ($locales[0] ?? 'en')));
    if (!in_array($default, $locales, true)) {
        array_unshift($locales, $default);
    }

    return array_values(array_filter($locales));
}

function resolve_locale(?string $preferred = null): string
{
    $enabled = locales_enabled();
    if ($preferred) {
        $preferred = strtolower(trim($preferred));
        if (in_array($preferred, $enabled, true)) {
            return $preferred;
        }
    }
    return $enabled[0] ?? 'en';
}

function __(
    string $key,
    string $default = '',
    ?string $locale = null
): string {
    $locale = $locale ? strtolower($locale) : (defined('APP_LOCALE') ? APP_LOCALE : resolve_locale());
    return Translator::phrase($key, $locale, $default !== '' ? $default : $key);
}

function base_url(string $path = ''): string
{
    $base = setting('site.url', getenv('APP_URL') ?: '');
    if (!$base) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base = $scheme . '://' . $host;
    }
    $base = rtrim($base, '/');
    if ($path === '') {
        return $base ?: '/';
    }
    return ($base ?: '') . '/' . ltrim($path, '/');
}

function asset_url(string $path): string
{
    return base_url('assets/' . ltrim($path, '/'));
}

function app_icon_url(int $size = 512): string
{
    $size = ($size === 192) ? 192 : 512;
    return base_url('icon.php?size=' . $size);
}

function current_url(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri  = $_SERVER['REQUEST_URI'] ?? '/';
    return $scheme . '://' . $host . $uri;
}

function redirect(string $url, int $status = 302): void
{
    header('Location: ' . $url, true, $status);
    exit;
}

function view(string $file, array $vars = []): void
{
    extract($vars);
    require app_path('Views/' . $file);
}

function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function abort(int $status = 404, string $message = ''): void
{
    http_response_code($status);

    $isNotFound = $status === 404;
    $view = $isNotFound ? 'errors/404.php' : 'errors/error.php';

    if ($message === '') {
        $message = $isNotFound
            ? __('error.404.body', 'The page you are looking for could not be found.')
            : __('error.generic.body', 'Something went wrong. Please try again later.');
    }

    view($view, [
        'status'  => $status,
        'message' => $message,
        'title'   => $isNotFound
            ? __('error.404.title', 'Page not found')
            : __('error.generic.title', 'Unexpected error'),
    ]);

    exit;
}

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_check(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    $token = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $isValid = isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string) $token);
    if (!$isValid) {
        http_response_code(400);
        exit('Invalid CSRF token');
    }
}

function cache_remember(string $key, int $ttl, callable $callback)
{
    $path = cache_file_path($key);
    if (is_file($path)) {
        $content = file_get_contents($path);
        $data = $content ? json_decode($content, true) : null;
        if (is_array($data) && ($data['expires_at'] ?? 0) >= time()) {
            return $data['value'];
        }
    }

    $value = $callback();
    $dir = dirname($path);
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    $payload = json_encode([
        'expires_at' => time() + max($ttl, 1),
        'value'      => $value,
    ]);
    @file_put_contents($path, $payload);
    return $value;
}

function cache_forget(string $key): void
{
    $path = cache_file_path($key);
    if (is_file($path)) {
        @unlink($path);
    }
}

function cache_file_path(string $key): string
{
    $hash = sha1($key);
    return storage_path('cache/' . substr($hash, 0, 2) . '/' . $hash . '.json');
}

function app_log(string $message, array $context = []): void
{
    $dir = storage_path('logs');
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message;
    if ($context) {
        $line .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    $line .= PHP_EOL;
    @file_put_contents($dir . '/app.log', $line, FILE_APPEND);
}

function paginator(int $page, int $per, int $total): void
{
    $pages = max(1, (int) ceil($total / max(1, $per)));
    if ($pages <= 1) {
        return;
    }
    echo '<nav class="tw-flex tw-flex-wrap tw-gap-2 tw-my-6" aria-label="Pagination">';
    for ($i = 1; $i <= $pages; $i++) {
        $isActive = $i === $page;
        $query = $_GET;
        $query['page'] = $i;
        $qs = http_build_query($query);
        $classes = 'tw-px-3 tw-py-1 tw-rounded tw-border tw-text-sm ' . ($isActive ? 'tw-bg-indigo-600 tw-border-indigo-600 tw-text-white' : 'tw-bg-white tw-text-gray-700 hover:tw-bg-slate-100');
        echo '<a class="' . $classes . '" href="?' . $qs . '">' . $i . '</a>';
    }
    echo '</nav>';
}

function percent(int $upVotes, int $downVotes): string
{
    $total = max(1, $upVotes + $downVotes);
    return number_format(($upVotes / $total) * 100, 1);
}
