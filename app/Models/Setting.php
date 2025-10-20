<?php
declare(strict_types=1);

namespace App\Models;

require_once __DIR__ . '/../db.php';

use PDO;

class Setting
{
    private static ?array $cache = null;

    private static function load(): array
    {
        if (self::$cache === null) {
            try {
                $stmt = db()->query('SELECT k, v FROM settings');
                $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_KEY_PAIR) : [];
                self::$cache = $rows ?: [];
            } catch (\Throwable $e) {
                self::$cache = [];
            }
        }
        return self::$cache;
    }

    public static function all(): array
    {
        return self::load();
    }

    public static function get(string $key, $default = '')
    {
        $all = self::load();
        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    public static function set(string $key, $value): void
    {
        $stmt = db()->prepare('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)');
        $stmt->execute([$key, $value]);
        self::reset();
    }

    public static function reset(): void
    {
        self::$cache = null;
    }
}
