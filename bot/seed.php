<?php
require __DIR__ . '/bootstrap.php';

use App\Support\Database;

$db = Database::connection();

$services = [
    ['Telegram', 'Telegram doğrulama', '5sim', 25.0, 1],
    ['WhatsApp', 'WhatsApp SMS doğrulaması', '5sim', 30.0, 1],
    ['Instagram', 'Instagram doğrulaması', 'sms_activate', 20.0, 0],
];

foreach ($services as $service) {
    $stmt = $db->prepare('INSERT OR IGNORE INTO services (name, description, provider, base_price, popular) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute($service);
}

$countries = [
    ['TR', 'Türkiye'],
    ['US', 'Amerika Birleşik Devletleri'],
    ['DE', 'Almanya'],
];

foreach ($countries as $country) {
    $stmt = $db->prepare('INSERT OR IGNORE INTO countries (code, name) VALUES (?, ?)');
    $stmt->execute($country);
}

$serviceCountries = [
    ['service' => 1, 'country' => 1, 'stock' => 10, 'price' => 25],
    ['service' => 1, 'country' => 2, 'stock' => 5, 'price' => 30],
    ['service' => 2, 'country' => 2, 'stock' => 8, 'price' => 35],
    ['service' => 3, 'country' => 3, 'stock' => 4, 'price' => 28],
];

foreach ($serviceCountries as $item) {
    $stmt = $db->prepare('INSERT OR IGNORE INTO service_countries (service_id, country_id, stock, price) VALUES (:service_id, :country_id, :stock, :price)');
    $stmt->execute([
        'service_id' => $item['service'],
        'country_id' => $item['country'],
        'stock' => $item['stock'],
        'price' => $item['price'],
    ]);
}

echo "Seed tamamlandı.\n";
