<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use function App\csrf_check;
use function App\csrf_token;
use function App\h;
use function App\login;
use function App\redirect;
use function App\current_user;

$pageTitle = 'Giriş Yap';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrf_check();
        login($_POST['identifier'] ?? '', $_POST['password'] ?? '');
        redirect('/');
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

if (current_user()) {
    redirect('/');
}

require __DIR__ . '/partials/header.php';
?>
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card shadow-sm border-0">
        <div class="card-body p-4">
          <h1 class="h4 fw-bold mb-3">SolveClone'a Giriş Yap</h1>
          <p class="text-muted">Profiline ulaş, sorularını yanıtla ve bildirimlerini takip et.</p>
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
        </div>
      </div>
    </div>
  </div>
<?php require __DIR__ . '/partials/footer.php';
