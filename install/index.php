<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Config\AppConfig;
use App\Database\Connection;
use App\Repositories\UserRepository;
use App\Support\Helpers;

$step = (int) ($_GET['step'] ?? 1);
$error = null;
$success = null;

if ($step === 1 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $dsn = trim((string) ($_POST['dsn'] ?? ''));
    $user = trim((string) ($_POST['user'] ?? ''));
    $pass = (string) ($_POST['pass'] ?? '');
    try {
        $pdo = new \PDO($dsn, $user, $pass, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
        file_put_contents(__DIR__ . '/../.env', "DB_DSN=$dsn\nDB_USER=$user\nDB_PASS=$pass\n");
        putenv('DB_DSN=' . $dsn);
        putenv('DB_USER=' . $user);
        putenv('DB_PASS=' . $pass);
        AppConfig::bootstrap();
        $success = 'Veritabanı bağlantısı başarılı. Kuruluma devam edebilirsin.';
        $step = 2;
    } catch (\Throwable $exception) {
        $error = 'Veritabanı bağlantısı başarısız: ' . $exception->getMessage();
    }
}

if ($step === 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = Connection::get();
    $pdo->beginTransaction();
    try {
        $sql = file_get_contents(__DIR__ . '/install.sql') ?: '';
        $pdo->exec($sql);
        $pdo->commit();
        $success = 'Tablolar başarıyla oluşturuldu.';
        $step = 3;
    } catch (\Throwable $exception) {
        $pdo->rollBack();
        $error = 'Kurulum başarısız: ' . $exception->getMessage();
    }
}

if ($step === 3 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = (string) $_POST['email'];
    $username = (string) $_POST['username'];
    $password = (string) $_POST['password'];
    try {
        $repo = new UserRepository();
        $now = Helpers::now();
        $repo->create([
            'email' => $email,
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'name' => 'Admin',
            'role' => 'admin',
            'questions_public' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $success = 'İlk admin kullanıcısı oluşturuldu.';
        $step = 4;
    } catch (\Throwable $exception) {
        $error = 'Admin oluşturulamadı: ' . $exception->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>SolveClone Kurulum</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<main class="container">
    <section class="card">
        <h1>Kurulum Sihirbazı</h1>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= App\Support\Helpers::escape($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= App\Support\Helpers::escape($success); ?></div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
            <form method="post" class="form">
                <label for="dsn">DSN</label>
                <input id="dsn" name="dsn" type="text" required value="mysql:host=localhost;dbname=solveclone;charset=utf8mb4">
                <label for="user">Veritabanı Kullanıcısı</label>
                <input id="user" name="user" type="text" required value="root">
                <label for="pass">Parola</label>
                <input id="pass" name="pass" type="password">
                <button type="submit" class="button button-primary">Bağlantıyı Test Et</button>
            </form>
        <?php elseif ($step === 2): ?>
            <form method="post" class="form">
                <p>Tabloları oluşturmak için devam et.</p>
                <button type="submit" class="button button-primary">Tabloları Kur</button>
            </form>
        <?php elseif ($step === 3): ?>
            <form method="post" class="form">
                <label for="email">Admin E-postası</label>
                <input id="email" name="email" type="email" required>
                <label for="username">Admin Kullanıcı Adı</label>
                <input id="username" name="username" type="text" required pattern="^[a-z0-9_]{3,32}$">
                <label for="password">Parola</label>
                <input id="password" name="password" type="password" minlength="8" required>
                <button type="submit" class="button button-primary">Admin Oluştur</button>
            </form>
        <?php else: ?>
            <p>Kurulum tamamlandı. Güvenlik için install klasörünü kaldırman önerilir.</p>
            <a href="/admin" class="button button-primary">Yönetim Paneline Git</a>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
