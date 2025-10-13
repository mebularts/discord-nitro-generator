<?php
namespace App\Support;

use PDO;
use PDOException;

class Database
{
    /** @var PDO|null */
    private static $connection = null;

    public static function boot(): void
    {
        if (self::$connection instanceof PDO) {
            return;
        }

        $dbPath = Config::get('database.path');
        $dir = dirname($dbPath);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $needMigrate = !file_exists($dbPath);

        $dsn = 'sqlite:' . $dbPath;

        try {
            self::$connection = new PDO($dsn);
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            throw new PDOException('Database connection failed: ' . $exception->getMessage());
        }

        if ($needMigrate) {
            self::migrate();
        } else {
            self::ensureSchemaUpToDate();
        }
    }

    public static function connection(): PDO
    {
        if (!self::$connection instanceof PDO) {
            self::boot();
        }

        return self::$connection;
    }

    private static function migrate(): void
    {
        $sql = file_get_contents(__DIR__ . '/../../storage/schema.sql');
        if ($sql === false) {
            throw new \RuntimeException('Failed to read migration file.');
        }

        self::$connection->exec($sql);
    }

    private static function ensureSchemaUpToDate(): void
    {
        $schemaChecks = [
            ['services', 'provider_service_id', 'ALTER TABLE services ADD COLUMN provider_service_id TEXT'],
            ['services', 'provider_category', 'ALTER TABLE services ADD COLUMN provider_category TEXT'],
            ['countries', 'provider_code', 'ALTER TABLE countries ADD COLUMN provider_code TEXT'],
            ['countries', 'dial_prefix', 'ALTER TABLE countries ADD COLUMN dial_prefix TEXT'],
            ['service_countries', 'provider_country_code', 'ALTER TABLE service_countries ADD COLUMN provider_country_code TEXT'],
        ];

        foreach ($schemaChecks as $check) {
            list($table, $column, $statement) = $check;
            if (!self::columnExists($table, $column)) {
                self::$connection->exec($statement);
            }
        }

        // Ensure unique constraint for provider_service_id if missing.
        $indexes = self::connection()->query("PRAGMA index_list('services')")->fetchAll(PDO::FETCH_ASSOC);
        $hasProviderIndex = false;
        foreach ($indexes as $index) {
            if (isset($index['name']) && $index['name'] === 'services_provider_service_id_unique') {
                $hasProviderIndex = true;
                break;
            }
        }
        if (!$hasProviderIndex) {
            self::$connection->exec('CREATE UNIQUE INDEX IF NOT EXISTS services_provider_service_id_unique ON services(provider_service_id)');
        }

        $countryIndexes = self::connection()->query("PRAGMA index_list('countries')")->fetchAll(PDO::FETCH_ASSOC);
        $hasCountryIndex = false;
        foreach ($countryIndexes as $index) {
            if (isset($index['name']) && $index['name'] === 'countries_provider_code_unique') {
                $hasCountryIndex = true;
                break;
            }
        }
        if (!$hasCountryIndex) {
            self::$connection->exec('CREATE UNIQUE INDEX IF NOT EXISTS countries_provider_code_unique ON countries(provider_code)');
        }
    }

    private static function columnExists(string $table, string $column): bool
    {
        $stmt = self::connection()->prepare("PRAGMA table_info('" . $table . "')");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $info) {
            if (($info['name'] ?? null) === $column) {
                return true;
            }
        }

        return false;
    }
}
