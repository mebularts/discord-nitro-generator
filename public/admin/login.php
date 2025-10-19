<?php
declare(strict_types=1);
session_start();
require_once __DIR__.'/../../app/helpers.php';

if (!empty($_SESSION['admin'])) {
  redirect('/admin/index.php');
}

$error = null;
if (is_post()) {
  csrf_check();
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  if ($username === '' || $password === '') {
    $error = 'Lütfen kullanıcı adı ve şifrenizi girin.';
  } else {
    $stmt = q($pdo, 'SELECT u.*, r.permissions FROM admin_users u LEFT JOIN admin_roles r ON r.id=u.role_id WHERE u.username=? LIMIT 1', [$username]);
    $user = $stmt->fetch();
    if ($user && (int)$user['is_active'] === 1 && password_verify($password, $user['password'])) {
      $permissions = [];
      if (!empty($user['is_super'])) {
        $permissions = array_keys(permission_labels());
      } else {
        $perms = json_decode((string)($user['permissions'] ?? '[]'), true);
        if (is_array($perms)) $permissions = $perms;
      }
      $_SESSION['admin'] = [
        'id'          => (int)$user['id'],
        'username'    => $user['username'],
        'full_name'   => $user['full_name'] ?? $user['username'],
        'role_id'     => $user['role_id'],
        'is_super'    => (int)$user['is_super'] === 1,
        'permissions' => $permissions,
      ];
      q($pdo, 'UPDATE admin_users SET last_login_at = NOW() WHERE id=?', [$user['id']]);
      redirect('/admin/index.php');
    } else {
      $error = 'Giriş bilgileri hatalı veya hesabınız pasif.';
    }
  }
}
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Yönetim Paneli · Giriş</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-900 flex items-center justify-center">
  <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">
    <div class="mb-6 text-center">
      <div class="text-sm text-slate-500">Yönetim Paneli</div>
      <h1 class="text-2xl font-semibold text-slate-800">Giriş Yap</h1>
    </div>
    <?php if ($error): ?>
      <div class="mb-4 px-4 py-3 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 text-sm"><?= h($error) ?></div>
    <?php endif; ?>
    <form method="post" class="space-y-4">
      <?= csrf_field() ?>
      <div>
        <label class="text-sm font-medium text-slate-600">Kullanıcı Adı</label>
        <input type="text" name="username" value="<?= h($_POST['username'] ?? '') ?>" class="mt-1 w-full border rounded-lg p-3 focus:outline-none focus:ring" autocomplete="username" required>
      </div>
      <div>
        <label class="text-sm font-medium text-slate-600">Şifre</label>
        <input type="password" name="password" class="mt-1 w-full border rounded-lg p-3 focus:outline-none focus:ring" autocomplete="current-password" required>
      </div>
      <button class="w-full px-4 py-3 rounded-lg bg-slate-900 text-white hover:bg-slate-800 transition">Giriş Yap</button>
    </form>
  </div>
</body>
</html>
