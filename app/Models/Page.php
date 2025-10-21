<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Page
{
    public static function findBySlug(string $slug): ?array
    {
        $stmt = db()->prepare('SELECT * FROM pages WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function all(): array
    {
        $stmt = db()->query('SELECT * FROM pages ORDER BY title ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
