<?php
namespace App\Models;

use App\Support\Database;
use PDO;

class UserRepository
{
    public function findByTelegramId(string $telegramId): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE telegram_id = :telegram_id LIMIT 1');
        $stmt->execute(['telegram_id' => $telegramId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function create(array $attributes): array
    {
        $sql = 'INSERT INTO users (telegram_id, username, first_name, last_name, balance) VALUES (:telegram_id, :username, :first_name, :last_name, :balance)';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'telegram_id' => $attributes['telegram_id'],
            'username' => $attributes['username'] ?? null,
            'first_name' => $attributes['first_name'] ?? null,
            'last_name' => $attributes['last_name'] ?? null,
            'balance' => $attributes['balance'] ?? 0,
        ]);

        return $this->find((int) Database::connection()->lastInsertId());
    }

    public function updateBalance(int $userId, float $balance): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET balance = :balance WHERE id = :id');
        $stmt->execute(['balance' => $balance, 'id' => $userId]);
    }

    public function incrementBalance(int $userId, float $amount): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET balance = balance + :amount WHERE id = :id');
        $stmt->execute(['amount' => $amount, 'id' => $userId]);
    }

    public function decrementBalance(int $userId, float $amount): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET balance = balance - :amount WHERE id = :id');
        $stmt->execute(['amount' => $amount, 'id' => $userId]);
    }

    public function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function all(): array
    {
        $stmt = Database::connection()->query('SELECT * FROM users ORDER BY created_at DESC');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
