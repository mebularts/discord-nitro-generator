<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use function App\csrf_check;
use function App\csrf_token;
use function App\db;
use function App\h;

$error = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrf_check();
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $username = trim($_POST['username'] ?? '');

        if ($email === '' || $password === '' || $username === '') {
            throw new RuntimeException('Tüm alanlar zorunludur.');
        }

        $installFile = __DIR__ . '/../../install/install.sql';
        if (!is_readable($installFile)) {
            throw new RuntimeException('Kurulum dosyası okunamadı.');
        }

        $sql = file_get_contents($installFile);
        if ($sql === false) {
            throw new RuntimeException('Kurulum dosyası yüklenemedi.');
        }

        $pdo = db();
        $pdo->beginTransaction();

        foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
            if ($statement === '') {
                continue;
            }
            $pdo->exec($statement);
        }

        $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, name, username, role, created_at, updated_at) VALUES (:email, :password_hash, :name, :username, :role, NOW(), NOW())');
        $stmt->execute([
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'name' => $username,
            'username' => $username,
            'role' => 'admin',
        ]);
        $pdo->commit();
        $success = true;
    } catch (\Throwable $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('[setup] ' . $e->getMessage());
        $error = $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="tr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kurulum | SolveClone</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/css/app.css">
    <meta http-equiv="Cache-Control" content="no-store">
  </head>
  <body class="bg-light">
    <main class="container py-5">
      <div class="row justify-content-center">
        <div class="col-lg-6">
          <div class="card shadow border-0">
            <div class="card-body p-4">
              <h1 class="h4 fw-bold mb-4">SolveClone Kurulum Sihirbazı</h1>
              <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                  <?= h($error) ?>
                </div>
              <?php elseif ($success): ?>
                <div class="alert alert-success" role="alert">
                  Kurulum tamamlandı! Yönetim paneline geçebilirsiniz.
                </div>
                <a class="btn btn-primary" href="/admin/login.php">Yönetim Paneline Git</a>
              <?php else: ?>
                <p class="text-muted">Veritabanı tabloları oluşturulacak ve ilk yönetici hesabı tanımlanacaktır.</p>
                <form method="post" novalidate>
                  <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                  <div class="mb-3">
                    <label for="email" class="form-label">Yönetici e-postası</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                  </div>
                  <div class="mb-3">
                    <label for="username" class="form-label">Kullanıcı adı</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                  </div>
                  <div class="mb-3">
                    <label for="password" class="form-label">Parola</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                  </div>
                  <button type="submit" class="btn btn-primary">Kurulumu Başlat</button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>
