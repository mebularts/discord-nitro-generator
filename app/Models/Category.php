<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

use function db;

class Category
{
    public static function all(): array
    {
        try {
            $stmt = db()->query('SELECT slug, name FROM categories ORDER BY sort_order ASC, name ASC');
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) {
            return [];
        }
    }
}
