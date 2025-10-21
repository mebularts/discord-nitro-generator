<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['admin'] = $user['id'];
        header('Location: /admin/dashboard.php');
        exit;
    }
    $error = 'Geçersiz e-posta ya da parola';
}
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Giriş · SolveClone</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= asset_url('css/app.css') ?>">
</head>
<body class="d-flex align-items-center justify-content-center bg-light" style="min-height:100vh;">
  <div class="card shadow-lg" style="max-width:420px;width:100%;">
    <div class="card-body p-4">
      <h1 class="h4 mb-3 text-center">SolveClone Yönetim</h1>
      <p class="text-muted small text-center mb-4"><?php echo __('admin.dashboard.subtitle', 'Monitor growth, translation health and engagement at a glance.'); ?></p>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
      <?php endif; ?>
      <form method="post" class="vstack gap-3">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <div>
          <label class="form-label">E-posta</label>
          <input type="email" name="email" class="form-control" required autofocus>
        </div>
        <div>
          <label class="form-label">Parola</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100">Giriş Yap</button>
      </form>
      <div class="mt-3 text-center">
        <a href="/admin/setup.php" class="small">İlk kurulum</a>
      </div>
    </div>
  </div>
</body>
</html>
