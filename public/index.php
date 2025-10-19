<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/notify.php';

$requestedLang = $_GET['lang'] ?? null;
if ($requestedLang) {
  $requestedLang = preg_replace('/[^a-zA-Z0-9_-]/', '', $requestedLang);
  set_current_language($pdo, $requestedLang);
}

$langCode   = current_language_code($pdo);
$site       = site_settings($pdo);
$theme      = theme_settings($pdo);
$assets     = custom_assets($pdo);
$languages  = available_languages($pdo);
$pageTitle  = $site['title'] ?? t('title');

$page = $_GET['p'] ?? 'home';
$allowed = ['home', 'book', 'date', 'time', 'confirm', 'done'];
if (!in_array($page, $allowed, true)) $page = 'home';
?><!doctype html>
<html lang="<?= h($langCode) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= h($pageTitle) ?> · v2.0</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    :root {
      --color-primary: <?= h($theme['primary']) ?>;
      --color-secondary: <?= h($theme['secondary']) ?>;
      --color-surface: <?= h($theme['surface']) ?>;
      --color-background: <?= h($theme['background']) ?>;
      --color-text: <?= h($theme['text']) ?>;
    }
    body {
      background-color: var(--color-background);
      color: var(--color-text);
    }
    .theme-surface { background-color: var(--color-surface); }
    .btn-primary {
      background-color: var(--color-primary);
      color: #fff;
    }
    .btn-primary:hover { filter: brightness(0.9); }
    .chip {
      background: rgba(255,255,255,0.15);
      border: 1px solid rgba(255,255,255,0.25);
      border-radius: 999px;
      padding: 0.25rem 0.75rem;
      font-size: 0.75rem;
    }
    .date-card {
      transition: all .2s ease;
    }
    .date-card.available:hover {
      border-color: var(--color-primary);
      color: var(--color-primary);
    }
    <?= $assets['css'] ?? '' ?>
  </style>
</head>
<body>
  <section class="relative overflow-hidden">
    <div class="absolute inset-0" style="background: linear-gradient(135deg, <?= h($theme['primary']) ?>, <?= h($theme['secondary']) ?>);"></div>
    <div class="relative max-w-7xl mx-auto px-3 py-8 text-white">
      <div class="flex items-center justify-between gap-3 flex-wrap">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-white/10 rounded-2xl flex items-center justify-center">🗓️</div>
          <div>
            <h1 class="text-2xl md:text-3xl font-semibold"><?= h($site['title'] ?? t('title')) ?> <span class="text-white/70">v2.0</span></h1>
            <p class="mt-1 text-white/80 text-sm md:text-base"><?= h($site['description'] ?? t('subtitle')) ?></p>
          </div>
        </div>
        <?php if ($languages): ?>
          <form method="get" class="flex items-center gap-2 text-sm">
            <?php foreach ($_GET as $k=>$v): if ($k==='lang') continue; ?><input type="hidden" name="<?= h($k) ?>" value="<?= h($v) ?>"><?php endforeach; ?>
            <label class="chip flex items-center gap-2">
              <span><?= h(t('language.switch')) ?>:</span>
              <select name="lang" class="bg-transparent border-none focus:outline-none text-white">
                <?php foreach ($languages as $code=>$lang): ?>
                  <option value="<?= h($code) ?>" <?= $code === $langCode ? 'selected' : '' ?>><?= h($lang['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <button class="chip" type="submit">OK</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <main class="max-w-7xl mx-auto px-4 mt-6">
    <div class="theme-surface rounded-2xl shadow-xl border border-slate-200/60 p-1">
      <?php
      // 1) Sayfa parçasını buffer içinde çalıştır (redirect varsa exit olur, hiç çıktı olmaz)
      if (in_array($page, ['book','date','time','confirm','done'])) {
        ob_start();
        include __DIR__ . '/_' . $page . '.php';
        $content = ob_get_clean();
        // 2) Progress bar'ı bas
        include __DIR__ . '/_progress.php';
        // 3) Ardından içerik
        echo $content;
      } else {
        include __DIR__ . '/_' . $page . '.php';
      }
      ?>
    </div>
  </main>

  <footer class="py-10 text-center text-slate-500 text-sm">
    <?= h($site['footer'] ?? t('footer.text')) ?>
  </footer>

  <?php if (!empty($assets['js'])): ?>
  <script><?= $assets['js'] ?></script>
  <?php endif; ?>
</body>
</html>
