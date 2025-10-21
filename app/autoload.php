<?php
declare(strict_types=1);

/**
 * Basit PSR-4 uyumlu autoloader.
 * Composer kullanılmadığı ortamlarda App\ namespace'ini otomatik olarak yükler.
 */
spl_autoload_register(static function (string $class): void {
    if (strpos($class, 'App\\') !== 0) {
        return;
    }

    $relativeClass = substr($class, 4);
    $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass);
    $file = __DIR__ . DIRECTORY_SEPARATOR . $relativePath . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});
