<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use RuntimeException;
use Throwable;

use function App\db;
use function App\notify;

final class Answer
{
    public static function create(int $questionId, int $userId, string $body, bool $isPublic): int
    {
        $body = trim($body);
        if ($body === '') {
            throw new RuntimeException('Cevap metni boş olamaz.');
        }

        $pdo = db();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare('INSERT INTO answers (question_id, user_id, body, is_public, created_at) VALUES (:question_id, :user_id, :body, :is_public, NOW())');
            $stmt->execute([
                'question_id' => $questionId,
                'user_id' => $userId,
                'body' => $body,
                'is_public' => $isPublic ? 1 : 0,
            ]);
            $answerId = (int) $pdo->lastInsertId();

            $questionStmt = $pdo->prepare('SELECT from_user_id, to_user_id FROM questions WHERE id = :id');
            $questionStmt->execute(['id' => $questionId]);
            $question = $questionStmt->fetch(PDO::FETCH_ASSOC);

            if ($question && $question['from_user_id']) {
                notify((int) $question['from_user_id'], 'new_answer', ['question_id' => $questionId, 'answer_id' => $answerId]);
            }

            $pdo->commit();

            return $answerId;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function updateVisibility(int $answerId, bool $isPublic): void
    {
        $stmt = db()->prepare('UPDATE answers SET is_public = :is_public, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['is_public' => $isPublic ? 1 : 0, 'id' => $answerId]);
    }

    public static function latestPublicFeed(): array
    {
        $sql = 'SELECT a.*, q.body AS question_body, u.username AS user_username, u.profile_color, u.avatar, u.questions_public
            FROM answers a
            JOIN questions q ON a.question_id = q.id
            JOIN users u ON a.user_id = u.id
            WHERE a.is_public = 1 AND u.questions_public = 1
            ORDER BY a.created_at DESC
            LIMIT 50';

        $stmt = db()->query($sql);

        return $stmt->fetchAll();
    }

    public static function findByQuestion(int $questionId): ?array
    {
        $stmt = db()->prepare('SELECT * FROM answers WHERE question_id = :question_id');
        $stmt->execute(['question_id' => $questionId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function forAdminList(): array
    {
        $sql = 'SELECT a.id, a.body, a.is_public, a.created_at, u.username
            FROM answers a
            JOIN users u ON a.user_id = u.id
            ORDER BY a.created_at DESC LIMIT 100';
        $stmt = db()->query($sql);

        return $stmt->fetchAll();
    }
}
