<?php
declare(strict_types=1);

use function App\csrf_token;
use function App\fetch_notifications;
use function App\h;
use function App\current_user;

require_once __DIR__ . '/../../app/bootstrap.php';

$user = current_user();
$notifications = $user ? fetch_notifications((int) $user['id'], 5) : [];
?>
<!doctype html>
<html lang="tr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($pageTitle) ? h($pageTitle) . ' | ' : '' ?>SolveClone</title>
    <meta name="description" content="SolveClone - modern bulmaca ve soru-cevap topluluğu.">
    <link rel="canonical" href="<?= h('https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $_SERVER['REQUEST_URI']) ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#3b82f6">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
  </head>
  <body>
    <nav class="navbar navbar-expand-lg bg-white border-bottom">
      <div class="container">
        <a class="navbar-brand brand-link" href="/">
          <span>SolveClone</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Menü">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item"><a class="nav-link" href="/">Ana Sayfa</a></li>
            <?php if ($user): ?>
              <li class="nav-item"><a class="nav-link" href="/inbox.php">Sorularım</a></li>
              <li class="nav-item"><a class="nav-link" href="/settings.php">Profil Ayarları</a></li>
            <?php endif; ?>
          </ul>
          <div class="d-flex align-items-center gap-3">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" role="switch" id="themeToggle" data-toggle="theme">
              <label class="form-check-label" for="themeToggle">Karanlık</label>
            </div>
            <?php if ($user): ?>
              <div class="dropdown">
                <button class="btn btn-outline-primary position-relative" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                  🔔
                  <?php $unread = array_reduce($notifications, function ($carry, $item) {
                      return $carry + (int) !$item['is_read'];
                  }, 0); ?>
                  <?php if ($unread > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $unread ?></span>
                  <?php endif; ?>
                </button>
                <div class="dropdown-menu dropdown-menu-end p-0" aria-labelledby="notificationDropdown" style="min-width: 280px;">
                  <div class="list-group list-group-flush">
                    <?php if (!$notifications): ?>
                      <div class="list-group-item text-center text-muted py-3">Bildirim yok</div>
                    <?php else: ?>
                      <?php foreach ($notifications as $notification): ?>
                        <div class="list-group-item small">
                          <div class="fw-semibold text-uppercase text-muted"><?= h($notification['type']) ?></div>
                          <div><?= h($notification['data_json']) ?></div>
                          <div class="text-muted"><?= h($notification['created_at']) ?></div>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                  <div class="p-2 border-top text-center">
                    <form method="post" action="/notifications_read.php">
                      <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                      <button class="btn btn-link btn-sm">Tümünü okundu say</button>
                    </form>
                  </div>
                </div>
              </div>
              <span class="text-muted small"><?= h($user['username']) ?></span>
              <form method="post" action="/logout.php" class="mb-0">
                <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                <button class="btn btn-outline-secondary btn-sm">Çıkış</button>
              </form>
            <?php else: ?>
              <a class="btn btn-outline-primary" href="/login.php">Giriş</a>
              <a class="btn btn-primary" href="/register.php">Kayıt Ol</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </nav>
    <main class="container py-4">
