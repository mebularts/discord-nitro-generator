<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use function App\csrf_check;
use function App\current_user;
use function App\mark_notifications_read;
use function App\redirect;

$user = current_user();
if (!$user) {
    redirect('/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrf_check();
        mark_notifications_read((int) $user['id']);
    } catch (\Throwable $e) {
        error_log('[notifications_read] ' . $e->getMessage());
    }
}

redirect($_SERVER['HTTP_REFERER'] ?? '/');
