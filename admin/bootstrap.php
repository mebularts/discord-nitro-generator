<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Models\User;

function admin_auth(): ?array
{
    if (!empty($_SESSION['admin_user'])) {
        return $_SESSION['admin_user'];
    }
    return null;
}

function require_admin(): void
{
    if (!admin_auth()) {
        redirect('/admin/login.php');
    }
}

function attempt_login(string $email, string $password): bool
{
    $user = User::findByEmail($email);
    if (!$user || !(bool) $user['is_active']) {
        return false;
    }

    if (!password_verify($password, $user['password_hash'])) {
        return false;
    }

    $_SESSION['admin_user'] = [
        'id'    => $user['id'],
        'name'  => $user['name'],
        'email' => $user['email'],
        'role'  => $user['role'],
    ];

    return true;
}

function logout_admin(): void
{
    unset($_SESSION['admin_user']);
}

function admin_view(string $template, array $data = []): string
{
    $viewPath = __DIR__ . '/views/' . $template . '.php';
    $layoutPath = __DIR__ . '/views/layout.php';

    if (!is_file($viewPath)) {
        throw new RuntimeException('Admin view missing: ' . $template);
    }

    $shared = [
        'admin'  => admin_auth(),
        'appName'=> setting('app_name', 'SolveClone'),
    ];

    extract(array_merge($shared, $data), EXTR_SKIP);

    ob_start();
    include $viewPath;
    $content = (string) ob_get_clean();

    ob_start();
    include $layoutPath;
    return (string) ob_get_clean();
}
