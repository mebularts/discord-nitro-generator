<?php
declare(strict_types=1);

namespace App;

use PDO;

use function App\db;

function notify(int $userId, string $type, array $payload): void
{
    $stmt = db()->prepare('INSERT INTO notifications (user_id, type, data_json, created_at) VALUES (:user_id, :type, :data_json, NOW())');
    $stmt->execute([
        'user_id' => $userId,
        'type' => $type,
        'data_json' => json_encode($payload, JSON_THROW_ON_ERROR),
    ]);
}

function fetch_notifications(int $userId, int $limit = 10): array
{
    $stmt = db()->prepare('SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit');
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function mark_notifications_read(int $userId, ?array $ids = null): void
{
    if ($ids === null) {
        $stmt = db()->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);

        return;
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $params = array_map('intval', $ids);
    $params[] = $userId;

    $sql = 'UPDATE notifications SET is_read = 1 WHERE id IN (' . $placeholders . ') AND user_id = ?';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
}
