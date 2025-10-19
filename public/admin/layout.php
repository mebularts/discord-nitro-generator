<?php
declare(strict_types=1);

function admin_nav_items(): array {
  $items = [
    ['id' => 'dashboard',    'label' => 'Gösterge Paneli', 'href' => '/admin/index.php',       'permission' => 'dashboard'],
    ['id' => 'appointments', 'label' => 'Randevular',      'href' => '/admin/appointments.php','permission' => 'appointments'],
    ['id' => 'customers',    'label' => 'Müşteriler',      'href' => '/admin/customers.php',   'permission' => 'customers'],
    ['id' => 'providers',    'label' => 'Randevu Verenler','href' => '/admin/providers.php',   'permission' => 'providers'],
    ['id' => 'notifications','label' => 'Bildirimler',     'href' => '/admin/notifications.php','permission' => 'notifications'],
    ['id' => 'settings',     'label' => 'Site Ayarları',   'href' => '/admin/settings.php',    'permission' => 'settings'],
    ['id' => 'languages',    'label' => 'Dil Yönetimi',    'href' => '/admin/languages.php',   'permission' => 'languages'],
    ['id' => 'translations', 'label' => 'Çeviri Metinleri','href' => '/admin/translations.php','permission' => 'translations'],
    ['id' => 'users',        'label' => 'Kullanıcılar',    'href' => '/admin/users.php',       'permission' => 'users'],
    ['id' => 'roles',        'label' => 'Roller',          'href' => '/admin/roles.php',       'permission' => 'users'],
  ];
  return array_values(array_filter($items, function ($item) {
    return empty($item['permission']) || has_permission($item['permission']);
  }));
}

function admin_render_header(string $title, string $active = 'dashboard'): void {
  global $pdo;
  $admin = current_admin();
  $site  = site_settings($pdo);
  $nav   = admin_nav_items();
  $lang  = current_language_code($pdo);
  ?>
  <!doctype html>
  <html lang="<?= h($lang) ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= h($title) ?> · Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
      body { background: #f1f5f9; }
      .admin-card { background: #fff; border-radius: 1rem; }
      .admin-nav a.active { background: rgba(59,130,246,0.1); color: #1d4ed8; }
    </style>
  </head>
  <body class="min-h-screen">
    <header class="bg-slate-900 text-white">
      <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
        <div>
          <div class="text-sm text-slate-300"><?= h($site['title'] ?? 'Randevu Sistemi') ?></div>
          <div class="text-lg font-semibold">Yönetim Paneli</div>
        </div>
        <div class="flex items-center gap-4 text-sm">
          <a href="/" class="px-3 py-1 rounded border border-white/30 hover:bg-white/10">Ön Yüz</a>
          <div class="text-slate-200">
            <?= h($admin['full_name'] ?? 'Admin') ?>
          </div>
          <a href="/admin/logout.php" class="px-3 py-1 rounded bg-rose-500 hover:bg-rose-600">Çıkış</a>
        </div>
      </div>
    </header>
    <div class="max-w-6xl mx-auto px-4 py-6">
      <nav class="admin-nav flex flex-wrap gap-2 mb-6">
        <?php foreach ($nav as $item): ?>
          <a href="<?= h($item['href']) ?>" class="px-3 py-2 rounded-lg border border-slate-200 text-sm <?= $active === $item['id'] ? 'active' : 'hover:bg-white' ?>">
            <?= h($item['label']) ?>
          </a>
        <?php endforeach; ?>
      </nav>
      <?php admin_render_flash(); ?>
      <div class="admin-card shadow-sm border border-slate-200">
        <div class="px-6 py-4 border-b border-slate-100">
          <h1 class="text-xl font-semibold text-slate-800"><?= h($title) ?></h1>
        </div>
        <div class="p-6 space-y-6">
  <?php
}

function admin_render_footer(): void {
  ?>
        </div>
      </div>
      <footer class="text-sm text-slate-500 mt-6 text-center">
        © <?= date('Y') ?> Yönetim Paneli
      </footer>
    </div>
  </body>
  </html>
  <?php
}

function admin_render_flash(): void {
  $types = ['success' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'error' => 'bg-rose-50 text-rose-700 border-rose-200'];
  foreach ($types as $type => $class) {
    $msg = get_flash($type);
    if ($msg) {
      echo '<div class="mb-4 px-4 py-3 border rounded-lg '.$class.'">'.h($msg).'</div>';
    }
  }
}
