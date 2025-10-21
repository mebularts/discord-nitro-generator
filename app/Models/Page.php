<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

use function db;

class Page
{
    public static function findBySlug(string $slug): ?array
    {
        $stmt = db()->prepare('SELECT * FROM pages WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function all(): array
    {
        $stmt = db()->query('SELECT slug, title, updated_at FROM pages ORDER BY slug ASC');
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
}
