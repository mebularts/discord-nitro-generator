<?php
declare(strict_types=1);

const BASE_PATH = __DIR__ . '/..';
const APP_PATH  = __DIR__;

require_once APP_PATH . '/helpers.php';
require_once APP_PATH . '/db.php';
require_once APP_PATH . '/Router.php';

// Autoloader for App namespace
spl_autoload_register(static function (string $class): void {
    if (strpos($class, 'App\\') !== 0) {
        return;
    }
    $relative = substr($class, 4);
    $path = APP_PATH . '/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require_once $path;
    }
});

// Load environment variables from .env
$envFile = BASE_PATH . '/.env';
if (is_file($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (!trim($line) || strpos(trim($line), '#') === 0) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if ($key !== '') {
            putenv($key . '=' . $value);
        }
    }
}

// Environment configuration
$env = getenv('APP_ENV') ?: 'production';
if ($env !== 'production') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
}

date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'UTC');

// Determine locale
$cookieLocale = $_COOKIE['solveclone_locale'] ?? null;
$locale = resolve_locale($cookieLocale ?: getenv('DEFAULT_LOCALE') ?: 'en');
define('APP_LOCALE', $locale);
setlocale(LC_ALL, APP_LOCALE);
