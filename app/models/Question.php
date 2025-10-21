<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use RuntimeException;

use function App\db;
use function App\notify;

final class Question
{
    public static function create(?int $fromUserId, int $toUserId, string $body, bool $isAnonymous): int
    {
        $body = trim($body);
        if ($body === '') {
            throw new RuntimeException('Soru metni boş olamaz.');
        }

        $stmt = db()->prepare('INSERT INTO questions (from_user_id, to_user_id, body, is_anonymous, created_at) VALUES (:from_user_id, :to_user_id, :body, :is_anonymous, NOW())');
        $stmt->execute([
            'from_user_id' => $isAnonymous ? null : $fromUserId,
            'to_user_id' => $toUserId,
            'body' => $body,
            'is_anonymous' => $isAnonymous ? 1 : 0,
        ]);

        $questionId = (int) db()->lastInsertId();
        notify($toUserId, 'new_question', [
            'question_id' => $questionId,
            'from_user_id' => $isAnonymous ? null : $fromUserId,
        ]);

        return $questionId;
    }

    public static function inbox(int $userId): array
    {
        $stmt = db()->prepare('SELECT q.*, u.username AS from_username FROM questions q LEFT JOIN users u ON q.from_user_id = u.id WHERE q.to_user_id = :id ORDER BY q.created_at DESC');
        $stmt->execute(['id' => $userId]);

        return $stmt->fetchAll();
    }

    public static function latestForProfile(int $userId): array
    {
        $stmt = db()->prepare('SELECT q.*, a.id AS answer_id, a.body AS answer_body, a.is_public, a.created_at AS answer_created_at,
                fu.username AS from_username
            FROM questions q
            LEFT JOIN answers a ON q.id = a.question_id
            LEFT JOIN users fu ON q.from_user_id = fu.id
            WHERE q.to_user_id = :user_id
            ORDER BY q.created_at DESC LIMIT 50');
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public static function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM questions WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function forAdminList(): array
    {
        $sql = 'SELECT q.id, q.body, q.is_anonymous, q.created_at, u.username AS to_username, f.username AS from_username
            FROM questions q
            JOIN users u ON q.to_user_id = u.id
            LEFT JOIN users f ON q.from_user_id = f.id
            ORDER BY q.created_at DESC LIMIT 100';
        $stmt = db()->query($sql);

        return $stmt->fetchAll();
    }
}
