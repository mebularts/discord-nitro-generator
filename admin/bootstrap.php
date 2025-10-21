<?php
declare(strict_types=1);

require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../app/bootstrap.php';

function admin_layout(string $title, callable $callback, array $options = []): void
{
    $siteName = setting('site.name', 'SolveClone');
    $active = $options['active'] ?? '';
    $subtitle = $options['subtitle'] ?? '';
    $alerts = $options['alerts'] ?? [];

    ob_start();
    $callback();
    $content = ob_get_clean();

    $navItems = [
        ['slug' => 'dashboard', 'label' => __('admin.nav.dashboard', 'Dashboard'), 'href' => '/admin/dashboard.php'],
        ['slug' => 'riddles', 'label' => __('admin.nav.riddles', 'Riddles'), 'href' => '/admin/riddles.php'],
        ['slug' => 'users', 'label' => __('admin.nav.users', 'Users'), 'href' => '/admin/users.php'],
        ['slug' => 'pages', 'label' => __('admin.nav.pages', 'Pages'), 'href' => '/admin/pages.php'],
        ['slug' => 'settings', 'label' => __('admin.nav.settings', 'Settings'), 'href' => '/admin/settings.php'],
    ];
    ?>
    <!doctype html>
    <html lang="tr">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title><?= h($title) ?> · <?= h($siteName) ?></title>
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
      <link rel="stylesheet" href="<?= asset_url('css/app.css') ?>">
    </head>
    <body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: var(--primary, #4f46e5);">
      <div class="container-fluid">
        <a class="navbar-brand" href="/admin/dashboard.php"><?= h($siteName) ?> Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav" aria-controls="adminNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNav">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <?php foreach ($navItems as $item): ?>
              <li class="nav-item">
                <a class="nav-link<?= $active === $item['slug'] ? ' active' : '' ?>" href="<?= h($item['href']) ?>"><?= h($item['label']) ?></a>
              </li>
            <?php endforeach; ?>
          </ul>
          <span class="navbar-text">
            <a href="/admin/logout.php" class="text-white text-decoration-none"><?= __('admin.nav.logout', 'Log out') ?></a>
          </span>
        </div>
      </div>
    </nav>

    <main class="container py-5">
      <header class="mb-4">
        <h1 class="h3 mb-1"><?= h($title) ?></h1>
        <?php if ($subtitle): ?><p class="text-muted mb-0"><?= h($subtitle) ?></p><?php endif; ?>
      </header>

      <?php foreach ($alerts as $alert): ?>
        <div class="alert alert-<?= h($alert['type'] ?? 'info') ?>"><?= $alert['message'] ?></div>
      <?php endforeach; ?>

      <?= $content ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
}
