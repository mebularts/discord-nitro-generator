
<?php require __DIR__.'/_auth.php'; require_once __DIR__.'/../app/db.php'; ?>
<!doctype html><html lang="tr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<title>Yönetim</title></head><body>
<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="/admin/dashboard.php">Yönetim</a>
    <div class="navbar-nav">
      <a class="nav-link" href="/admin/riddles.php">Sorular</a>
      <a class="nav-link" href="/admin/users.php">Kullanıcılar</a>
      <a class="nav-link" href="/admin/pages.php">Sayfalar</a>
      <a class="nav-link" href="/admin/settings.php">Ayarlar</a>
    </div>
  </div>
</nav>
<div class="container py-4">
  <div class="row g-3">
    <?php
    $pdo=db();
    $riddleCount=(int)$pdo->query("SELECT COUNT(*) FROM riddles")->fetchColumn();
    $userCount=(int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    ?>
    <div class="col-md-3"><div class="card"><div class="card-body">
      <div class="fw-bold">Toplam Soru</div><div class="display-6"><?= $riddleCount ?></div>
    </div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body">
      <div class="fw-bold">Kullanıcı</div><div class="display-6"><?= $userCount ?></div>
    </div></div></div>
  </div>
</div>
</body></html>
