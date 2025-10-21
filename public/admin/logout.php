<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use function App\csrf_check;
use function App\logout;
use function App\redirect;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrf_check();
        logout();
    } catch (Throwable $e) {
        error_log('[logout] ' . $e->getMessage());
    }
}

redirect('/admin/login.php');
