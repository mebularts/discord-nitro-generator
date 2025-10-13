<?php
namespace App\Models;

use App\Support\Database;
use PDO;

class ServiceRepository
{
    public function all(bool $onlyPopular = false): array
    {
        $query = 'SELECT * FROM services';
        if ($onlyPopular) {
            $query .= ' WHERE popular = 1';
        }
        $query .= ' ORDER BY popular DESC, base_price ASC, name ASC';

        $stmt = Database::connection()->query($query);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search(string $keyword): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM services WHERE name LIKE :keyword OR provider_service_id LIKE :keyword ORDER BY popular DESC, name ASC');
        $stmt->execute(['keyword' => "%$keyword%"]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM services WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $service = $stmt->fetch(PDO::FETCH_ASSOC);

        return $service ?: null;
    }

    public function findByProviderId(string $providerId): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM services WHERE provider_service_id = :provider_id LIMIT 1');
        $stmt->execute(['provider_id' => $providerId]);
        $service = $stmt->fetch(PDO::FETCH_ASSOC);

        return $service ?: null;
    }

    public function upsert(array $service): array
    {
        $existing = $this->findByProviderId($service['provider_service_id']);
        if ($existing) {
            $stmt = Database::connection()->prepare('UPDATE services SET name = :name, description = :description, provider = :provider, base_price = :base_price, popular = :popular, provider_category = :provider_category WHERE id = :id');
            $stmt->execute([
                'name' => $service['name'],
                'description' => $service['description'] ?? null,
                'provider' => $service['provider'],
                'base_price' => $service['base_price'],
                'popular' => $service['popular'] ?? 0,
                'provider_category' => $service['provider_category'] ?? null,
                'id' => $existing['id'],
            ]);

            return $this->find((int) $existing['id']);
        }

        $stmt = Database::connection()->prepare('INSERT INTO services (provider_service_id, name, description, provider, base_price, popular, provider_category) VALUES (:provider_service_id, :name, :description, :provider, :base_price, :popular, :provider_category)');
        $stmt->execute([
            'provider_service_id' => $service['provider_service_id'],
            'name' => $service['name'],
            'description' => $service['description'] ?? null,
            'provider' => $service['provider'],
            'base_price' => $service['base_price'],
            'popular' => $service['popular'] ?? 0,
            'provider_category' => $service['provider_category'] ?? null,
        ]);

        return $this->find((int) Database::connection()->lastInsertId());
    }
}
