<?php
declare(strict_types=1);

use function App\asset_url;
use function App\canonical_url;
use function App\csrf_token;
use function App\fetch_notifications;
use function App\h;
use function App\meta_tags;
use function App\current_user;

require_once __DIR__ . '/../../app/bootstrap.php';

$user = current_user();
$notifications = $user ? fetch_notifications((int) $user['id'], 5) : [];
$metaOverrides = isset($pageMeta) && is_array($pageMeta) ? $pageMeta : [];
if (isset($pageTitle)) {
    $metaOverrides['title'] = $pageTitle;
}
$meta = meta_tags($metaOverrides);
$fullTitle = $meta['title'] === 'SolveClone' ? 'SolveClone' : $meta['title'] . ' | SolveClone';
$structuredData = json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebApplication',
    'name' => 'SolveClone',
    'url' => canonical_url(),
    'applicationCategory' => 'GameApplication',
    'operatingSystem' => 'Web',
    'inLanguage' => 'tr-TR',
    'description' => $meta['description'],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<!doctype html>
<html lang="tr" data-app>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= h($fullTitle) ?></title>
    <meta name="description" content="<?= h($meta['description']) ?>">
    <meta name="keywords" content="<?= h($meta['keywords']) ?>">
    <meta name="robots" content="<?= h($meta['robots']) ?>">
    <meta name="language" content="tr">
    <meta name="author" content="SolveClone">
    <meta name="theme-color" content="#3b82f6" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#1e293b" media="(prefers-color-scheme: dark)">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SolveClone">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">
    <link rel="canonical" href="<?= h(canonical_url()) ?>">
    <link rel="icon" type="image/svg+xml" href="<?= h(asset_url('images/icon-192.svg')) ?>">
    <link rel="apple-touch-icon" href="<?= h(asset_url('images/icon-192.svg')) ?>">
    <link rel="mask-icon" href="<?= h(asset_url('images/icon-192.svg')) ?>" color="#3b82f6">
    <link rel="manifest" href="/manifest.webmanifest">
    <meta property="og:title" content="<?= h($fullTitle) ?>">
    <meta property="og:description" content="<?= h($meta['description']) ?>">
    <meta property="og:url" content="<?= h(canonical_url()) ?>">
    <meta property="og:site_name" content="SolveClone">
    <meta property="og:type" content="website">
    <meta property="og:image" content="<?= h($meta['image']) ?>">
    <meta property="og:image:alt" content="SolveClone Uygulaması">
    <meta property="og:locale" content="tr_TR">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= h($fullTitle) ?>">
    <meta name="twitter:description" content="<?= h($meta['description']) ?>">
    <meta name="twitter:image" content="<?= h($meta['image']) ?>">
    <meta name="twitter:site" content="@solveclone">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= h(asset_url('css/app.css')) ?>">
    <?php if ($structuredData !== false): ?>
      <script type="application/ld+json"><?= $structuredData ?></script>
    <?php endif; ?>
  </head>
  <body class="app-shell">
    <nav class="navbar navbar-expand-lg shadow-sm app-navbar sticky-top">
      <div class="container">
        <a class="navbar-brand brand-link" href="/">
          <span class="brand-icon" aria-hidden="true">🧩</span>
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
            <div class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" role="switch" id="themeToggle" data-toggle="theme">
              <label class="form-check-label" for="themeToggle">Karanlık</label>
            </div>
            <?php if ($user): ?>
              <div class="dropdown">
                <button class="btn btn-outline-primary position-relative" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                  <span class="visually-hidden">Bildirimler</span>
                  <span aria-hidden="true">🔔</span>
                  <?php $unread = array_reduce($notifications, static function ($carry, $item) {
                      return $carry + (int) !$item['is_read'];
                  }, 0); ?>
                  <?php if ($unread > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $unread ?></span>
                  <?php endif; ?>
                </button>
                <div class="dropdown-menu dropdown-menu-end p-0" aria-labelledby="notificationDropdown" style="min-width: 300px;">
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
              <span class="text-muted small d-none d-md-inline"><?= h($user['username']) ?></span>
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
    <main class="container py-4 flex-grow-1">
