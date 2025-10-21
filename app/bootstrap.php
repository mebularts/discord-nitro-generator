<?php
/**
 * Application bootstrap file.
 */

declare(strict_types=1);

const BASE_PATH = __DIR__ . '/..';
const APP_PATH  = __DIR__;

require_once APP_PATH . '/helpers.php';
require_once APP_PATH . '/db.php';
require_once APP_PATH . '/Router.php';

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

// Load environment file if available.
$envPath = BASE_PATH . '/.env';
if (is_file($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if ($key !== '') {
            putenv($key . '=' . $value);
        }
    }
}

// Start session for CSRF + flash messaging.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$timezone = env('APP_TIMEZONE', 'UTC');
if (@date_default_timezone_set($timezone) === false) {
    date_default_timezone_set('UTC');
}

$environment = env('APP_ENV', 'production');
if ($environment === 'production') {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

// Locale resolution
$requestedLocale = $_GET['lang'] ?? $_COOKIE['solveclone_locale'] ?? env('DEFAULT_LOCALE', 'en');
$locale = resolve_locale($requestedLocale);
if (!isset($_COOKIE['solveclone_locale']) || $_COOKIE['solveclone_locale'] !== $locale) {
    setcookie('solveclone_locale', $locale, time() + (86400 * 365), '/');
}

define('APP_LOCALE', $locale);

translator()->setLocale($locale);

// Inject view globals
view_share([
    'appName'      => setting('app_name', 'SolveClone'),
    'appLocale'    => $locale,
    'availableLocales' => available_locales(),
    'currentRoute' => null,
    'meta'         => [
        'title'       => setting('meta_title', 'SolveClone'),
        'description' => setting('meta_description', 'Solve clever riddles from around the world.'),
        'image'       => app_icon_url('512x512'),
    ],
]);
