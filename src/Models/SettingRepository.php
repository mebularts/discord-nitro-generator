<?php
namespace App\Models;

use App\Support\Database;
use PDO;

class SettingRepository
{
    public function get(string $name, $default = null)
    {
        $stmt = Database::connection()->prepare('SELECT value FROM settings WHERE name = :name LIMIT 1');
        $stmt->execute(['name' => $name]);
        $value = $stmt->fetchColumn();

        return $value !== false ? $value : $default;
    }

    public function set(string $name, $value): void
    {
        $stmt = Database::connection()->prepare('INSERT INTO settings (name, value) VALUES (:name, :value)
            ON CONFLICT(name) DO UPDATE SET value = excluded.value');
        $stmt->execute(['name' => $name, 'value' => $value]);
    }

    public function all(): array
    {
        $stmt = Database::connection()->query('SELECT name, value FROM settings ORDER BY name');
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    }
}
