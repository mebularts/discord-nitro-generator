<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database\Connection;
use PDO;

final class NotificationRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::get();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO notifications (user_id, type, data_json, is_read, created_at) VALUES (:user_id, :type, :data_json, :is_read, :created_at)');
        $stmt->execute([
            'user_id' => $data['user_id'],
            'type' => $data['type'],
            'data_json' => $data['data_json'],
            'is_read' => $data['is_read'] ?? 0,
            'created_at' => $data['created_at'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function latestForUser(int $userId, int $limit = 20): array
    {
        $stmt = $this->db->prepare('SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit');
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function markAsRead(int $userId, ?array $ids = null): int
    {
        if ($ids === null) {
            $stmt = $this->db->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND is_read = 0');
            $stmt->execute(['user_id' => $userId]);
            return $stmt->rowCount();
        }
        if ($ids === []) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND id IN ($placeholders)");
        $stmt->execute(array_merge([$userId], $ids));
        return $stmt->rowCount();
    }

    public function unreadCount(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0');
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }
}
