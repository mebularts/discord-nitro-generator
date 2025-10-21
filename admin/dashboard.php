<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_admin();

$stats = [
    'riddles' => (int) db()->query('SELECT COUNT(*) FROM riddles')->fetchColumn(),
    'users'   => (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'pages'   => (int) db()->query('SELECT COUNT(*) FROM pages')->fetchColumn(),
];

$latestRiddles = db()->query('SELECT title, slug, published_at FROM riddles ORDER BY published_at DESC LIMIT 5')->fetchAll() ?: [];

echo admin_view('dashboard', [
    'title' => 'Dashboard',
    'active' => 'dashboard',
    'stats' => $stats,
    'latestRiddles' => $latestRiddles,
]);
