<?php

declare(strict_types=1);

namespace App\Database;

use App\Config\AppConfig;
use PDO;
use PDOException;
use RuntimeException;

final class Connection
{
    private static ?PDO $pdo = null;

    public static function get(): PDO
    {
        if (self::$pdo === null) {
            try {
                $pdo = new PDO(
                    AppConfig::get('DB_DSN'),
                    AppConfig::get('DB_USER'),
                    AppConfig::get('DB_PASS'),
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
                self::$pdo = $pdo;
            } catch (PDOException $exception) {
                throw new RuntimeException('Veritabanı bağlantısı başarısız: ' . $exception->getMessage(), 0, $exception);
            }
        }

        return self::$pdo;
    }
}
