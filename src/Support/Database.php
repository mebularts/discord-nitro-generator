<?php
namespace App\Support;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function boot(): void
    {
        if (self::$connection instanceof PDO) {
            return;
        }

        $dbPath = Config::get('database.path');
        $dir = dirname($dbPath);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $needMigrate = !file_exists($dbPath);

        $dsn = 'sqlite:' . $dbPath;

        try {
            self::$connection = new PDO($dsn);
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            throw new PDOException('Database connection failed: ' . $exception->getMessage());
        }

        if ($needMigrate) {
            self::migrate();
        }
    }

    public static function connection(): PDO
    {
        if (!self::$connection instanceof PDO) {
            self::boot();
        }

        return self::$connection;
    }

    private static function migrate(): void
    {
        $sql = file_get_contents(__DIR__ . '/../../storage/schema.sql');
        if ($sql === false) {
            throw new \RuntimeException('Failed to read migration file.');
        }

        self::$connection->exec($sql);
    }
}
