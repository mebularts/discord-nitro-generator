<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database\Connection;
use PDO;

final class QuestionRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::get();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO questions (from_user_id, to_user_id, body, is_anonymous, created_at) VALUES (:from_user_id, :to_user_id, :body, :is_anonymous, :created_at)');
        $stmt->execute([
            'from_user_id' => $data['from_user_id'],
            'to_user_id' => $data['to_user_id'],
            'body' => $data['body'],
            'is_anonymous' => $data['is_anonymous'],
            'created_at' => $data['created_at'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findInbox(int $userId, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare('SELECT SQL_CALC_FOUND_ROWS q.*, u.username AS from_username FROM questions q LEFT JOIN users u ON u.id = q.from_user_id WHERE q.to_user_id = :user_id ORDER BY q.created_at DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();
        $total = (int) $this->db->query('SELECT FOUND_ROWS()')->fetchColumn();
        return ['items' => $items, 'total' => $total];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM questions WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $question = $stmt->fetch();
        return $question ?: null;
    }

    public function deleteById(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM questions WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
