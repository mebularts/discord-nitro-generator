<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database\Connection;
use PDO;

final class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::get();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByEmailOrUsername(string $identifier): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :identifier OR username = :identifier LIMIT 1');
        $stmt->execute(['identifier' => $identifier]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO users (email, password_hash, username, name, bio, profile_color, avatar, social_links, questions_public, username_changed_at, role, created_at, updated_at) VALUES (:email, :password_hash, :username, :name, :bio, :profile_color, :avatar, :social_links, :questions_public, :username_changed_at, :role, :created_at, :updated_at)');
        $stmt->execute([
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
            'username' => $data['username'],
            'name' => $data['name'] ?? null,
            'bio' => $data['bio'] ?? null,
            'profile_color' => $data['profile_color'] ?? null,
            'avatar' => $data['avatar'] ?? null,
            'social_links' => $data['social_links'] ?? null,
            'questions_public' => $data['questions_public'] ?? 1,
            'username_changed_at' => $data['username_changed_at'] ?? null,
            'role' => $data['role'] ?? 'user',
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $columns = [];
        $params = ['id' => $id];
        foreach ($data as $key => $value) {
            $columns[] = sprintf('%s = :%s', $key, $key);
            $params[$key] = $value;
        }
        if ($columns === []) {
            return;
        }

        $sql = 'UPDATE users SET ' . implode(', ', $columns) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function paginatePublicAnswers(int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare('SELECT SQL_CALC_FOUND_ROWS a.*, q.body AS question_body, u.username, u.name, u.profile_color FROM answers a INNER JOIN users u ON u.id = a.user_id INNER JOIN questions q ON q.id = a.question_id WHERE a.is_public = 1 AND u.questions_public = 1 ORDER BY a.created_at DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();
        $total = (int) $this->db->query('SELECT FOUND_ROWS()')->fetchColumn();
        return ['items' => $items, 'total' => $total];
    }

    public function all(int $page, int $perPage, ?string $search = null): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $where = '';
        if ($search) {
            $where = 'WHERE username LIKE :search OR email LIKE :search';
            $params['search'] = '%' . $search . '%';
        }
        $stmt = $this->db->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM users $where ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();
        $total = (int) $this->db->query('SELECT FOUND_ROWS()')->fetchColumn();
        return ['items' => $items, 'total' => $total];
    }
}
