<?php
require __DIR__ . '/bootstrap.php';

use App\Services\CatalogSyncService;

$catalog = new CatalogSyncService();

try {
    $services = $catalog->syncAll();
    echo sprintf("%d servis senkronize edildi.\n", count($services));
} catch (\Throwable $exception) {
    echo 'Senkronizasyon başarısız: ' . $exception->getMessage() . "\n";
    exit(1);
}

echo "Seed tamamlandı.\n";
