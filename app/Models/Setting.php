<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Setting
{
    public static function value(string $key): ?string
    {
        $stmt = db()->prepare('SELECT value FROM settings WHERE `key` = :key LIMIT 1');
        $stmt->execute(['key' => $key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['value'] ?? null;
    }

    public static function set(string $key, string $value): void
    {
        $stmt = db()->prepare('INSERT INTO settings (`key`, `value`) VALUES (:key, :value)
            ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)');
        $stmt->execute(['key' => $key, 'value' => $value]);
    }

    public static function all(): array
    {
        $stmt = db()->query('SELECT `key`, `value` FROM settings ORDER BY `key` ASC');
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    }
}
