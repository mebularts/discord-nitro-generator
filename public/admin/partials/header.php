<?php
declare(strict_types=1);

use function App\h;
use function App\csrf_token;
use function App\current_user;

require_once __DIR__ . '/../../../app/bootstrap.php';
$user = current_user();
?>
<!doctype html>
<html lang="tr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Yönetim Paneli | SolveClone</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/css/app.css">
    <meta http-equiv="Cache-Control" content="no-store">
  </head>
  <body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container-fluid">
        <a class="navbar-brand" href="/admin/dashboard.php">SolveClone Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav" aria-controls="adminNav" aria-expanded="false" aria-label="Menüyü aç">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNav">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item"><a class="nav-link" href="/admin/dashboard.php">Panel</a></li>
            <li class="nav-item"><a class="nav-link" href="/admin/users.php">Kullanıcılar</a></li>
            <li class="nav-item"><a class="nav-link" href="/admin/questions.php">Sorular</a></li>
            <li class="nav-item"><a class="nav-link" href="/admin/answers.php">Cevaplar</a></li>
            <li class="nav-item"><a class="nav-link" href="/admin/notifications.php">Bildirimler</a></li>
            <li class="nav-item"><a class="nav-link" href="/admin/settings.php">Ayarlar</a></li>
          </ul>
          <?php if ($user): ?>
            <form method="post" action="/admin/logout.php" class="d-inline">
              <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
              <button class="btn btn-outline-light btn-sm">Çıkış</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </nav>
    <main class="container my-4">
