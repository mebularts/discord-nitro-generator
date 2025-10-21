<?php
$siteName = setting('site.name', getenv('SITE_NAME') ?: 'SolveClone');
$metaDesc = setting('seo.meta_description', __('meta.description', 'Modern, multilingual riddle platform.'));
$primary = setting('theme.primary_color', getenv('THEME_PRIMARY') ?: '#4f46e5');
$themeColor = setting('pwa.theme_color', getenv('PWA_THEME_COLOR') ?: $primary);
$backgroundColor = setting('pwa.background_color', getenv('PWA_BG_COLOR') ?: '#ffffff');
$lang = defined('APP_LOCALE') ? APP_LOCALE : resolve_locale();
$enabledLocales = locales_enabled();
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$canonical = base_url(ltrim($currentPath, '/'));
if ($currentPath === '/') {
    $canonical = rtrim(base_url('/'), '/');
}
$ogImage = setting('site.logo_url') ?: app_icon_url(512);
$customHeadCss = setting('custom.head_css');
$customHeadJs = setting('custom.head_js');
$customBodyCss = setting('custom.body_css');
$customBodyJs = setting('custom.body_js');
$headerAd = setting('ads.header');
$sidebarAd = setting('ads.sidebar');
$inlineAd = setting('ads.inline');
$footerAd = setting('ads.footer');
$title = $title ?? '';
$fullTitle = $title ? $title . ' · ' . $siteName : $siteName;
?>
<!doctype html>
<html lang="<?= h($lang) ?>" class="tw-scroll-smooth">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($fullTitle) ?></title>
<meta name="description" content="<?= h($metaDesc) ?>">
<link rel="canonical" href="<?= h($canonical) ?>">
<meta property="og:title" content="<?= h($fullTitle) ?>">
<meta property="og:description" content="<?= h($metaDesc) ?>">
<meta property="og:url" content="<?= h(current_url()) ?>">
<meta property="og:site_name" content="<?= h($siteName) ?>">
<meta property="og:image" content="<?= h($ogImage) ?>">
<meta name="theme-color" content="<?= h($themeColor) ?>">
<meta name="apple-mobile-web-app-status-bar-style" content="<?= h($themeColor) ?>">
<?php foreach ($enabledLocales as $locale): ?>
<link rel="alternate" href="<?= h($canonical) ?>" hreflang="<?= h($locale) ?>">
<?php endforeach; ?>
<link rel="manifest" href="/manifest.webmanifest">
<link rel="apple-touch-icon" href="<?= app_icon_url(192) ?>">
<link rel="icon" type="image/png" sizes="192x192" href="<?= app_icon_url(192) ?>">
<link rel="icon" type="image/png" sizes="512x512" href="<?= app_icon_url(512) ?>">

<script>
  window.tailwind = { config: { prefix: 'tw-' } };
  (function(){
    const storedTheme = window.localStorage.getItem('solveclone:theme');
    if (storedTheme) {
      document.documentElement.dataset.theme = storedTheme;
      document.body?.setAttribute('data-theme', storedTheme);
    }
  })();
  window.__solveLocale = <?= json_encode($lang) ?>;
  window.__solveTranslations = <?= json_encode([
    'pwa.banner.android' => __('pwa.banner.android', 'Tap “Install” to pin SolveClone to your Android home screen.'),
    'pwa.banner.ios' => __('pwa.banner.ios', 'Tap the share icon → “Add to Home Screen” on iOS to install.'),
    'pwa.banner.desktop' => __('pwa.banner.desktop', 'Use the browser install button to keep SolveClone one click away.'),
  ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="<?= asset_url('css/app.css') ?>">
<style>
:root { --primary: <?= h($primary) ?>; }
</style>
<?= $customHeadCss ?>
<?= $customHeadJs ?>
</head>
<body class="tw-bg-slate-50 tw-text-gray-900" data-theme="light">
<header class="tw-bg-white/90 tw-backdrop-blur tw-border-b tw-sticky tw-top-0 tw-z-40">
  <div class="tw-max-w-6xl tw-mx-auto tw-px-4 tw-py-4 tw-flex tw-flex-wrap tw-gap-4 tw-items-center tw-justify-between">
    <div class="tw-flex tw-items-center tw-gap-3">
      <a href="/" class="tw-inline-flex tw-items-center tw-gap-2 tw-text-xl tw-font-semibold tw-text-gray-900">
        <span class="tw-inline-block tw-w-8 tw-h-8 tw-rounded-xl tw-bg-[var(--primary)] tw-flex tw-items-center tw-justify-center tw-text-white tw-font-semibold">S</span>
        <span><?= h($siteName) ?></span>
      </a>
      <nav class="tw-hidden md:tw-flex tw-items-center tw-gap-4 tw-text-sm tw-font-medium">
        <a href="/" class="nav-link tw-p-0 tw-text-gray-600 hover:tw-text-gray-900"><?= __('nav.home', 'Home') ?></a>
        <a href="/new" class="nav-link tw-p-0 tw-text-gray-600 hover:tw-text-gray-900"><?= __('nav.new', 'Latest') ?></a>
        <a href="/terms" class="nav-link tw-p-0 tw-text-gray-600 hover:tw-text-gray-900"><?= __('nav.terms', 'Terms') ?></a>
        <a href="/privacy" class="nav-link tw-p-0 tw-text-gray-600 hover:tw-text-gray-900"><?= __('nav.privacy', 'Privacy') ?></a>
        <a href="/contact" class="nav-link tw-p-0 tw-text-gray-600 hover:tw-text-gray-900"><?= __('nav.contact', 'Contact') ?></a>
      </nav>
    </div>
    <div class="tw-flex tw-items-center tw-gap-3">
      <form method="get" action="/" class="tw-hidden lg:tw-flex tw-items-center tw-gap-2">
        <label class="tw-sr-only" for="navSearch">Search</label>
        <input id="navSearch" type="search" name="q" value="<?= h($_GET['q'] ?? '') ?>" placeholder="<?= __('home.search_placeholder', 'Search riddles…') ?>" class="form-control form-control-sm">
      </form>
      <div class="dropdown">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <?= strtoupper($lang) ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <?php foreach ($enabledLocales as $locale): ?>
            <li><a class="dropdown-item" href="?lang=<?= h($locale) ?>"><?= strtoupper($locale) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <button type="button" class="btn btn-sm btn-outline-primary" data-action="toggle-theme" aria-label="<?= __('theme.toggle', 'Toggle theme') ?>">
        🌗
      </button>
      <a href="/admin" class="btn btn-sm btn-outline-secondary tw-hidden lg:tw-inline-flex"><?= __('nav.admin', 'Admin') ?></a>
    </div>
  </div>
  <div class="tw-block md:tw-hidden tw-px-4 tw-pb-3">
    <div class="tw-flex tw-items-center tw-gap-2">
      <form method="get" action="/" class="tw-flex-1">
        <label class="tw-sr-only" for="navSearchMobile">Search</label>
        <input id="navSearchMobile" type="search" name="q" value="<?= h($_GET['q'] ?? '') ?>" placeholder="<?= __('home.search_placeholder', 'Search riddles…') ?>" class="form-control form-control-sm">
      </form>
      <a href="/app" class="btn btn-sm btn-primary"><?= __('nav.app', 'Install App') ?></a>
    </div>
  </div>
  <?php if ($headerAd): ?>
    <div class="tw-border-t tw-bg-slate-50">
      <div class="tw-max-w-6xl tw-mx-auto tw-px-4 tw-py-2 tw-text-center">
        <?= $headerAd ?>
      </div>
    </div>
  <?php endif; ?>
</header>

<main class="tw-max-w-6xl tw-mx-auto tw-px-4 tw-pt-6 tw-pb-12">
  <section id="installPrompt" class="tw-hidden tw-mb-6 tw-bg-white tw-border tw-border-indigo-100 tw-rounded-2xl tw-shadow-sm tw-p-5 tw-flex tw-flex-col lg:tw-flex-row tw-gap-4 tw-justify-between tw-items-start">
    <div>
      <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900"><?= __('pwa.banner.title', 'Install SolveClone') ?></h2>
      <p id="platformTip" class="tw-text-sm tw-text-gray-600 tw-mt-1"><?= __('pwa.banner.subtitle', 'Add SolveClone to your home screen for an app-like, offline-ready experience.') ?></p>
    </div>
    <div class="tw-flex tw-items-center tw-gap-3">
      <button type="button" class="btn btn-primary" data-action="install-pwa"><?= __('btn.install', 'Install') ?></button>
      <a href="/app" class="btn btn-outline-secondary btn-sm"><?= __('nav.app', 'Install App') ?></a>
    </div>
  </section>

  <div class="lg:tw-grid lg:tw-grid-cols-[minmax(0,1fr)_320px] lg:tw-gap-6">
    <div class="tw-space-y-6">
      <?php if (!empty($inlineAd)): ?>
        <div class="tw-bg-white tw-border tw-rounded-2xl tw-p-4 tw-text-center">
          <?= $inlineAd ?>
        </div>
      <?php endif; ?>
      <?php if (isset($content)) { echo $content; } elseif (isset($yield)) { call_user_func($yield); } ?>
    </div>
    <aside class="tw-mt-8 lg:tw-mt-0">
      <div class="tw-sticky tw-top-28 tw-space-y-4">
        <div class="tw-bg-white tw-border tw-rounded-2xl tw-p-5 tw-shadow-sm">
          <h2 class="tw-text-sm tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wide tw-mb-3">SolveClone</h2>
          <p class="tw-text-sm tw-text-gray-600"><?= h($metaDesc) ?></p>
        </div>
        <?php if (!empty($sidebarAd)): ?>
          <div class="tw-bg-white tw-border tw-rounded-2xl tw-p-4 tw-text-center">
            <?= $sidebarAd ?>
          </div>
        <?php endif; ?>
      </div>
    </aside>
  </div>
</main>

<footer class="tw-bg-white tw-border-t">
  <div class="tw-max-w-6xl tw-mx-auto tw-px-4 tw-py-8 tw-text-sm tw-text-gray-600 tw-space-y-4">
    <?php if ($footerAd): ?>
      <div class="tw-text-center tw-bg-slate-50 tw-rounded-2xl tw-p-3">
        <?= $footerAd ?>
      </div>
    <?php endif; ?>
    <div class="tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-3">
      <span>&copy; <?= date('Y') ?> <?= h($siteName) ?> · <?= __('footer.rights', 'All rights reserved.') ?></span>
      <div class="tw-flex tw-gap-3">
        <a href="/terms" class="tw-text-gray-500 hover:tw-text-gray-800"><?= __('nav.terms', 'Terms') ?></a>
        <a href="/privacy" class="tw-text-gray-500 hover:tw-text-gray-800"><?= __('nav.privacy', 'Privacy') ?></a>
        <a href="/advertising" class="tw-text-gray-500 hover:tw-text-gray-800"><?= __('nav.advertising', 'Advertising') ?></a>
      </div>
    </div>
    <?= $customBodyCss ?>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset_url('js/app.js') ?>" defer></script>
<?= $customBodyJs ?>
</body>
</html>
