<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    with_old(['email' => $email]);

    if (attempt_login($email, $password)) {
        redirect('/admin/dashboard.php');
    }

    $error = 'Invalid credentials';
} else {
    if (admin_auth()) {
        redirect('/admin/dashboard.php');
    }
    $error = null;
}

$appName = setting('app_name', 'SolveClone');

require __DIR__ . '/views/login.php';
