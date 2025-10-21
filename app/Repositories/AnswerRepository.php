<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database\Connection;
use PDO;

final class AnswerRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::get();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO answers (question_id, user_id, body, is_public, created_at, updated_at) VALUES (:question_id, :user_id, :body, :is_public, :created_at, :updated_at)');
        $stmt->execute([
            'question_id' => $data['question_id'],
            'user_id' => $data['user_id'],
            'body' => $data['body'],
            'is_public' => $data['is_public'],
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findByQuestionId(int $questionId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM answers WHERE question_id = :question_id LIMIT 1');
        $stmt->execute(['question_id' => $questionId]);
        $answer = $stmt->fetch();
        return $answer ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM answers WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $answer = $stmt->fetch();
        return $answer ?: null;
    }

    public function updateVisibility(int $answerId, int $isPublic): void
    {
        $stmt = $this->db->prepare('UPDATE answers SET is_public = :is_public, updated_at = :updated_at WHERE id = :id');
        $stmt->execute([
            'is_public' => $isPublic,
            'updated_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'id' => $answerId,
        ]);
    }

    public function findPublicByUser(int $userId, int $page, int $perPage, string $sort): array
    {
        $offset = ($page - 1) * $perPage;
        $orderBy = $sort === 'popular' ? 'a.created_at DESC' : 'a.created_at DESC';
        $stmt = $this->db->prepare("SELECT SQL_CALC_FOUND_ROWS a.*, q.body AS question_body FROM answers a INNER JOIN questions q ON q.id = a.question_id INNER JOIN users u ON u.id = a.user_id WHERE a.user_id = :user_id AND a.is_public = 1 AND u.questions_public = 1 ORDER BY $orderBy LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();
        $total = (int) $this->db->query('SELECT FOUND_ROWS()')->fetchColumn();
        return ['items' => $items, 'total' => $total];
    }

    public function deleteByQuestionId(int $questionId): void
    {
        $stmt = $this->db->prepare('DELETE FROM answers WHERE question_id = :question_id');
        $stmt->execute(['question_id' => $questionId]);
    }
}
