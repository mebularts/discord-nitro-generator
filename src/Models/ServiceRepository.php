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
        $query .= ' ORDER BY popular DESC, name ASC';

        $stmt = Database::connection()->query($query);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search(string $keyword): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM services WHERE name LIKE :keyword ORDER BY popular DESC, name ASC');
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
}
