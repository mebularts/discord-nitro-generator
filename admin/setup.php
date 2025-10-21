<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

$schema = file_get_contents(__DIR__ . '/../install/install.sql');
$pdo = db();
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $pdo->exec($schema);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, name, role) VALUES (?, ?, ?, "admin")');
    $stmt->execute([$email, $hash, 'Admin']);
    $message = 'Kurulum tamamlandı. Yönetim paneline giriş yapabilirsiniz.';
}
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SolveClone Kurulum</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= asset_url('css/app.css') ?>">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-6">
        <div class="card shadow-lg">
          <div class="card-body p-4">
            <h1 class="h4 mb-3 text-center">SolveClone Kurulum Sihirbazı</h1>
            <p class="text-muted small text-center mb-4">Veritabanı tabloları oluşturulacak ve ilk admin hesabı eklenecektir.</p>
            <?php if ($message): ?>
              <div class="alert alert-success"><?= h($message) ?></div>
            <?php endif; ?>
            <form method="post" class="vstack gap-3">
              <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
              <div>
                <label class="form-label">Admin e-posta</label>
                <input type="email" name="email" class="form-control" value="admin@example.com" required>
              </div>
              <div>
                <label class="form-label">Admin parola</label>
                <input type="password" name="password" class="form-control" value="admin123" required>
              </div>
              <button class="btn btn-primary w-100">Kurulumu Başlat</button>
            </form>
            <div class="mt-3 text-center">
              <a href="/admin/login.php" class="small">Giriş sayfasına dön</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
