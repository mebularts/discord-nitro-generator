<?php
namespace App\Models;

use App\Support\Database;
use PDO;

class PaymentRepository
{
    public function create(array $attributes): array
    {
        $sql = 'INSERT INTO payments (user_id, method, amount, status, payload) VALUES (:user_id, :method, :amount, :status, :payload)';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'user_id' => $attributes['user_id'],
            'method' => $attributes['method'],
            'amount' => $attributes['amount'],
            'status' => $attributes['status'] ?? 'pending',
            'payload' => $attributes['payload'] ?? null,
        ]);

        return $this->find((int) Database::connection()->lastInsertId());
    }

    public function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM payments WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        return $payment ?: null;
    }

    public function update(int $paymentId, array $attributes): void
    {
        $columns = [];
        $params = ['id' => $paymentId];

        foreach ($attributes as $key => $value) {
            $columns[] = "$key = :$key";
            $params[$key] = $value;
        }

        if (!$columns) {
            return;
        }

        $sql = 'UPDATE payments SET ' . implode(', ', $columns) . ', updated_at = CURRENT_TIMESTAMP WHERE id = :id';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
    }

    public function all(): array
    {
        $stmt = Database::connection()->query('SELECT p.*, u.telegram_id FROM payments p JOIN users u ON u.id = p.user_id ORDER BY p.created_at DESC');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByOrderId(string $orderId): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM payments WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => (int) $orderId]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        return $payment ?: null;
    }
}
