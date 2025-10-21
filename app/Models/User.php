<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class User
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, role, is_active, created_at)
            VALUES (:name, :email, :password, :role, :active, NOW())');
        $stmt->execute([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role'     => $data['role'] ?? 'admin',
            'active'   => $data['is_active'] ?? 1,
        ]);

        return (int) db()->lastInsertId();
    }

    public static function all(): array
    {
        $stmt = db()->query('SELECT id, name, email, role, is_active, created_at FROM users ORDER BY created_at DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function updateStatus(int $id, bool $active): void
    {
        $stmt = db()->prepare('UPDATE users SET is_active = :active WHERE id = :id');
        $stmt->execute(['active' => $active ? 1 : 0, 'id' => $id]);
    }
}
