<?php

declare(strict_types=1);

ini_set('display_errors', getenv('APP_ENV') === 'dev' ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/logs/app.log');

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', __DIR__);

autoload();

use App\Config\AppConfig;
use App\Support\Session;
use App\Security\CsrfTokenManager;
use App\Support\Helpers;

AppConfig::bootstrap();

Session::start();

CsrfTokenManager::ensureToken();

Helpers::registerErrorHandler();

function autoload(): void
{
    spl_autoload_register(static function (string $class): void {
        if (strpos($class, 'App\\') !== 0) {
            return;
        }

        $relative = substr($class, 4);
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
        $file = __DIR__ . DIRECTORY_SEPARATOR . $relativePath;
        if (is_file($file)) {
            require_once $file;
        }
    });

    require_once __DIR__ . '/Support/helpers.php';
}
