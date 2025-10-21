<?php
declare(strict_types=1);

namespace App;

use RuntimeException;

function ensureSessionStarted(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start([
            'cookie_httponly' => true,
            'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'cookie_samesite' => 'Lax',
        ]);
    }
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    ensureSessionStarted();

    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_check(): void
{
    ensureSessionStarted();

    $token = $_POST['_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(400);
        throw new RuntimeException('Geçersiz CSRF token');
    }
}

function request_scheme(): string
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return 'https';
    }

    return 'http';
}

function absolute_url(string $path = ''): string
{
    $path = '/' . ltrim($path, '/');
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return request_scheme() . '://' . $host . $path;
}

function canonical_url(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';

    return absolute_url($uri);
}

function asset_url(string $path, bool $absolute = false): string
{
    $path = ltrim($path, '/');
    $relative = '/assets/' . $path;
    $fullPath = dirname(__DIR__) . '/public' . $relative;

    $version = '';
    if (is_file($fullPath)) {
        $mtime = filemtime($fullPath);
        if ($mtime !== false) {
            $version = '?v=' . $mtime;
        }
    }

    $url = $relative . $version;

    return $absolute ? absolute_url($url) : $url;
}

function meta_tags(array $overrides = []): array
{
    $defaults = [
        'title' => 'SolveClone',
        'description' => 'SolveClone: Modern bulmaca, soru-cevap topluluğu ve gelişmiş yönetim paneli ile PWA deneyimi.',
        'image' => asset_url('images/social-card.svg', true),
        'robots' => 'index, follow',
        'keywords' => 'bulmaca, quiz, soru cevap, pwa, solveclone',
    ];

    $meta = array_merge($defaults, array_filter($overrides, static function ($value): bool {
        return $value !== null && $value !== '';
    }));

    if (strpos($meta['image'], 'http://') !== 0 && strpos($meta['image'], 'https://') !== 0) {
        $meta['image'] = absolute_url($meta['image']);
    }

    return $meta;
}

function redirect(string $to, int $code = 302): void
{
    header('Location: ' . $to, true, $code);
    exit;
}
