<?php
declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = getenv('DB_DSN') ?: 'mysql:host=127.0.0.1;dbname=solveclone;charset=utf8mb4';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        error_log('[db] ' . $e->getMessage());
        http_response_code(500);
        exit('Veritabanı bağlantısı kurulamadı. Lütfen daha sonra tekrar deneyin.');
    }

    return $pdo;
}
