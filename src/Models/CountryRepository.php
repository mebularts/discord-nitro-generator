<?php
namespace App\Models;

use App\Support\Database;
use PDO;

class CountryRepository
{
    public function allForService(int $serviceId): array
    {
        $sql = 'SELECT c.*, sc.stock, sc.price FROM service_countries sc JOIN countries c ON c.id = sc.country_id WHERE sc.service_id = :service_id AND sc.stock > 0 ORDER BY c.name';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['service_id' => $serviceId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search(string $keyword): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM countries WHERE name LIKE :keyword OR code LIKE :keyword ORDER BY name ASC');
        $stmt->execute(['keyword' => "%$keyword%"]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM countries WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $country = $stmt->fetch(PDO::FETCH_ASSOC);

        return $country ?: null;
    }
}
