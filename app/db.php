<?php

declare(strict_types=1);


/**
 * Return a shared PDO instance.
 */
function db(): PDO
{
    static $pdo;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = env('DB_DSN');
    if (!$dsn) {
        $driver = env('DB_DRIVER', 'mysql');
        $host   = env('DB_HOST', '127.0.0.1');
        $port   = env('DB_PORT', $driver === 'mysql' ? '3306' : '5432');
        $dbname = env('DB_DATABASE', 'solveclone');
        $charset = env('DB_CHARSET', 'utf8mb4');

        if ($driver === 'sqlite') {
            $dsn = 'sqlite:' . BASE_PATH . '/' . env('DB_DATABASE', 'storage/database.sqlite');
        } else {
            $dsn = sprintf('%s:host=%s;port=%s;dbname=%s;charset=%s', $driver, $host, $port, $dbname, $charset);
        }
    }

    $username = env('DB_USERNAME');
    $password = env('DB_PASSWORD');

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $username, $password, $options);
    } catch (PDOException $exception) {
        log_error('Database connection failed: ' . $exception->getMessage());
        abort(500, 'Database connection failed.');
    }

    return $pdo;
}
