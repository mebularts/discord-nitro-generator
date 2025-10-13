<?php
namespace App\Models;

use App\Support\Database;
use PDO;

class OrderRepository
{
    public function create(array $attributes): array
    {
        $sql = 'INSERT INTO orders (user_id, service_id, country_id, provider, provider_order_id, phone_number, status, cost) VALUES (:user_id, :service_id, :country_id, :provider, :provider_order_id, :phone_number, :status, :cost)';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'user_id' => $attributes['user_id'],
            'service_id' => $attributes['service_id'],
            'country_id' => $attributes['country_id'],
            'provider' => $attributes['provider'],
            'provider_order_id' => $attributes['provider_order_id'] ?? null,
            'phone_number' => $attributes['phone_number'] ?? null,
            'status' => $attributes['status'] ?? 'pending',
            'cost' => $attributes['cost'] ?? 0,
        ]);

        return $this->find((int) Database::connection()->lastInsertId());
    }

    public function update(int $orderId, array $attributes): void
    {
        $columns = [];
        $params = ['id' => $orderId];

        foreach ($attributes as $key => $value) {
            $columns[] = "$key = :$key";
            $params[$key] = $value;
        }

        if (!$columns) {
            return;
        }

        $sql = 'UPDATE orders SET ' . implode(', ', $columns) . ', updated_at = CURRENT_TIMESTAMP WHERE id = :id';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
    }

    public function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM orders WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        return $order ?: null;
    }

    public function forUser(int $userId): array
    {
        $sql = 'SELECT o.*, s.name AS service_name, c.name AS country_name '
            . 'FROM orders o '
            . 'JOIN services s ON s.id = o.service_id '
            . 'JOIN countries c ON c.id = o.country_id '
            . 'WHERE o.user_id = :user_id '
            . 'ORDER BY o.created_at DESC';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
