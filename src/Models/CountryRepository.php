<?php
namespace App\Models;

use App\Support\Database;
use App\Support\Pricing;
use PDO;

class CountryRepository
{
    public function allForService(int $serviceId): array
    {
        $sql = 'SELECT c.*, sc.stock, sc.price, sc.provider_country_code FROM service_countries sc JOIN countries c ON c.id = sc.country_id WHERE sc.service_id = :service_id AND sc.stock >= 0 ORDER BY sc.stock DESC, c.name';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['service_id' => $serviceId]);

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($records as &$record) {
            $basePrice = (float) ($record['price'] ?? 0);
            $record['provider_price'] = $basePrice;
            $record['price'] = Pricing::applyMarkup($basePrice);
        }
        unset($record);

        return $records;
    }

    public function search(string $keyword): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM countries WHERE name LIKE :keyword OR code LIKE :keyword OR provider_code LIKE :keyword ORDER BY name ASC');
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

    public function findByProviderCode(string $code): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM countries WHERE provider_code = :code LIMIT 1');
        $stmt->execute(['code' => $code]);
        $country = $stmt->fetch(PDO::FETCH_ASSOC);

        return $country ?: null;
    }

    public function upsert(array $country): array
    {
        $existing = $this->findByProviderCode($country['provider_code']);
        if ($existing) {
            $stmt = Database::connection()->prepare('UPDATE countries SET code = :code, name = :name, dial_prefix = :dial_prefix WHERE id = :id');
            $stmt->execute([
                'code' => $country['code'],
                'name' => $country['name'],
                'dial_prefix' => $country['dial_prefix'] ?? null,
                'id' => $existing['id'],
            ]);

            return $this->find((int) $existing['id']);
        }

        $stmt = Database::connection()->prepare('INSERT INTO countries (code, name, provider_code, dial_prefix) VALUES (:code, :name, :provider_code, :dial_prefix)');
        $stmt->execute([
            'code' => $country['code'],
            'name' => $country['name'],
            'provider_code' => $country['provider_code'],
            'dial_prefix' => $country['dial_prefix'] ?? null,
        ]);

        return $this->find((int) Database::connection()->lastInsertId());
    }

    public function syncServiceCountry(int $serviceId, int $countryId, array $data): void
    {
        $stmt = Database::connection()->prepare('INSERT INTO service_countries (service_id, country_id, stock, price, provider_country_code) VALUES (:service_id, :country_id, :stock, :price, :provider_country_code)
            ON CONFLICT(service_id, country_id) DO UPDATE SET stock = excluded.stock, price = excluded.price, provider_country_code = excluded.provider_country_code');
        $stmt->execute([
            'service_id' => $serviceId,
            'country_id' => $countryId,
            'stock' => $data['stock'],
            'price' => $data['price'],
            'provider_country_code' => $data['provider_country_code'] ?? null,
        ]);
    }
}
