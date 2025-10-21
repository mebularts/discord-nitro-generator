<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use function App\csrf_check;
use function App\csrf_token;
use function App\h;
use function App\redirect;
use function App\current_user;
use function App\login;

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrf_check();
        login($_POST['identifier'] ?? '', $_POST['password'] ?? '');
        redirect('/admin/dashboard.php');
    } catch (\Throwable $e) {
        $error = $e->getMessage();
    }
}

if (current_user()) {
    redirect('/admin/dashboard.php');
}
?>
<!doctype html>
<html lang="tr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Yönetim Girişi | SolveClone</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/css/app.css">
    <meta http-equiv="Cache-Control" content="no-store">
  </head>
  <body class="bg-light">
    <main class="container py-5">
      <div class="row justify-content-center">
        <div class="col-md-5">
          <div class="card shadow-sm border-0">
            <div class="card-body p-4">
              <h1 class="h4 fw-bold mb-3 text-center">SolveClone Yönetici Girişi</h1>
              <?php if ($error): ?>
                <div class="alert alert-danger" role="alert"><?= h($error) ?></div>
              <?php endif; ?>
              <form method="post" novalidate>
                <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                <div class="mb-3">
                  <label class="form-label" for="identifier">E-posta veya kullanıcı adı</label>
                  <input type="text" class="form-control" id="identifier" name="identifier" required autocomplete="username">
                </div>
                <div class="mb-3">
                  <label class="form-label" for="password">Parola</label>
                  <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn-primary w-100">Giriş Yap</button>
              </form>
              <div class="mt-3 text-center">
                <a href="/">Siteye geri dön</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>
