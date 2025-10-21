<?php

declare(strict_types=1);


if (is_file(__DIR__ . '/../.env')) {
    header('Location: /admin/login.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = trim($_POST['db_host'] ?? '127.0.0.1');
    $dbPort = trim($_POST['db_port'] ?? '3306');
    $dbName = trim($_POST['db_name'] ?? 'solveclone');
    $dbUser = trim($_POST['db_user'] ?? 'root');
    $dbPass = $_POST['db_pass'] ?? '';
    $adminName = trim($_POST['admin_name'] ?? 'Administrator');
    $adminEmail = trim($_POST['admin_email'] ?? 'admin@example.com');
    $adminPassword = $_POST['admin_password'] ?? '';

    if (!$adminEmail || !$adminPassword) {
        $errors[] = 'Admin credentials are required.';
    }

    if (empty($errors)) {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $dbHost, $dbPort, $dbName);
        try {
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $sql = file_get_contents(__DIR__ . '/../install/install.sql');
            $pdo->exec($sql);

            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, is_active, created_at)
                VALUES (:name, :email, :password, "admin", 1, NOW())
                ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)');
            $stmt->execute([
                'name'     => $adminName,
                'email'    => $adminEmail,
                'password' => password_hash($adminPassword, PASSWORD_DEFAULT),
            ]);

            $env = <<<ENV
APP_ENV=production
APP_TIMEZONE=UTC
DEFAULT_LOCALE=en
DB_DRIVER=mysql
DB_HOST={$dbHost}
DB_PORT={$dbPort}
DB_DATABASE={$dbName}
DB_USERNAME={$dbUser}
DB_PASSWORD={$dbPass}
DEEPL_AUTH_KEY=
ENV;
            file_put_contents(__DIR__ . '/../.env', $env);
            $success = true;
        } catch (Throwable $e) {
            $errors[] = 'Installation failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SolveClone Installer</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { min-height: 100vh; background: radial-gradient(circle at top, #14b8a6, #0f172a); display: flex; align-items: center; justify-content: center; font-family: 'Inter', system-ui, sans-serif; }
        .card { background: rgba(15, 23, 42, 0.9); border: 1px solid rgba(148, 163, 184, 0.3); border-radius: 1.5rem; padding: 2rem; width: min(720px, 95%); }
    </style>
</head>
<body>
<div class="card shadow-lg">
    <h1 class="h3 text-white mb-3">SolveClone Installer</h1>
    <p class="text-secondary">Provide your database connection and admin credentials to finish the setup.</p>

    <?php if ($success): ?>
        <div class="alert alert-success">Installation completed! <a href="/admin/login.php" class="alert-link">Proceed to login</a>.</div>
    <?php endif; ?>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endforeach; ?>

    <form method="post" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Database host</label>
            <input type="text" class="form-control" name="db_host" value="<?= htmlspecialchars($_POST['db_host'] ?? '127.0.0.1', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Database port</label>
            <input type="text" class="form-control" name="db_port" value="<?= htmlspecialchars($_POST['db_port'] ?? '3306', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Database name</label>
            <input type="text" class="form-control" name="db_name" value="<?= htmlspecialchars($_POST['db_name'] ?? 'solveclone', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Database user</label>
            <input type="text" class="form-control" name="db_user" value="<?= htmlspecialchars($_POST['db_user'] ?? 'root', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Database password</label>
            <input type="password" class="form-control" name="db_pass" value="<?= htmlspecialchars($_POST['db_pass'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-12"><hr class="border-secondary"></div>
        <div class="col-md-6">
            <label class="form-label">Admin name</label>
            <input type="text" class="form-control" name="admin_name" value="<?= htmlspecialchars($_POST['admin_name'] ?? 'Administrator', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Admin email</label>
            <input type="email" class="form-control" name="admin_email" value="<?= htmlspecialchars($_POST['admin_email'] ?? 'admin@example.com', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Admin password</label>
            <input type="password" class="form-control" name="admin_password" required>
        </div>
        <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary">Install</button>
        </div>
    </form>
</div>
</body>
</html>
