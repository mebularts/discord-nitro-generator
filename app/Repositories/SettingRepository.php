<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database\Connection;
use PDO;

final class SettingRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::get();
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $stmt = $this->db->prepare('SELECT value FROM settings WHERE `key` = :key LIMIT 1');
        $stmt->execute(['key' => $key]);
        $value = $stmt->fetchColumn();
        return $value !== false ? (string) $value : $default;
    }

    public function set(string $key, string $value): void
    {
        $stmt = $this->db->prepare('REPLACE INTO settings (`key`, value) VALUES (:key, :value)');
        $stmt->execute(['key' => $key, 'value' => $value]);
    }

    public function all(): array
    {
        $stmt = $this->db->query('SELECT `key`, value FROM settings');
        $items = [];
        foreach ($stmt->fetchAll() as $row) {
            $items[$row['key']] = $row['value'];
        }
        return $items;
    }
}
