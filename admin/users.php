<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_admin();

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    if (isset($_POST['toggle'])) {
        $id = (int) $_POST['toggle'];
        $active = (int) $_POST['active'] === 1;
        $pdo->prepare('UPDATE users SET is_active = :active WHERE id = :id')->execute(['active' => $active ? 1 : 0, 'id' => $id]);
        flash('success', 'User updated');
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role = $_POST['role'] ?? 'editor';
        if ($name && $email && $password) {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, is_active, created_at) VALUES (:name, :email, :password, :role, 1, NOW())');
            $stmt->execute([
                'name'     => $name,
                'email'    => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role'     => $role,
            ]);
            flash('success', 'User created');
        }
    }
    redirect('/admin/users.php');
}

$users = $pdo->query('SELECT id, name, email, role, is_active, created_at FROM users ORDER BY created_at DESC')->fetchAll() ?: [];

echo admin_view('users', [
    'title' => 'Users',
    'active' => 'users',
    'users' => $users,
    'flash' => flash('success'),
]);
