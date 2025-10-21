<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use function App\csrf_check;
use function App\csrf_token;
use function App\h;
use function App\redirect;
use function App\register;
use function App\current_user;

$pageTitle = 'Kayıt Ol';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrf_check();
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $username = $_POST['username'] ?? '';
        $name = $_POST['name'] ?? '';
        register($email, $password, $username, $name);
        App\login($email, $password);
        redirect('/');
    } catch (\Throwable $e) {
        $error = $e->getMessage();
    }
}

if (current_user()) {
    redirect('/');
}

require __DIR__ . '/partials/header.php';
?>
  <div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
      <div class="card shadow-sm border-0">
        <div class="card-body p-4">
          <h1 class="h4 fw-bold mb-3">SolveClone'a Katıl</h1>
          <p class="text-muted">Profilini özelleştir, sorular gönder ve toplulukla etkileşime geç.</p>
          <?php if ($error): ?>
            <div class="alert alert-danger" role="alert"><?= h($error) ?></div>
          <?php endif; ?>
          <form method="post" novalidate>
            <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label" for="name">Ad Soyad</label>
                <input type="text" class="form-control" id="name" name="name" autocomplete="name">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="username">Kullanıcı adı</label>
                <input type="text" class="form-control" id="username" name="username" required autocomplete="username">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="email">E-posta</label>
                <input type="email" class="form-control" id="email" name="email" required autocomplete="email">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="password">Parola</label>
                <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password">
              </div>
            </div>
            <button type="submit" class="btn btn-primary mt-4 w-100">Kayıt Ol</button>
          </form>
        </div>
      </div>
    </div>
  </div>
<?php require __DIR__ . '/partials/footer.php';
