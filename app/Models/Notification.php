<?php
declare(strict_types=1);

namespace App\Models;

use function App\db;

final class Notification
{
    public static function latest(): array
    {
        $stmt = db()->query('SELECT * FROM notifications ORDER BY created_at DESC LIMIT 100');

        return $stmt->fetchAll();
    }
}
