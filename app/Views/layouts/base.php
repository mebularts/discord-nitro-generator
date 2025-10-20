<?php
$siteName = setting('site.name', getenv('SITE_NAME') ?: 'SolveClone');
$metaDesc = setting('seo.meta_description', getenv('SEO_META_DESCRIPTION') ?: 'Modern, çok dilli bulmaca platformu.');
$primary  = setting('theme.primary_color', getenv('THEME_PRIMARY') ?: '#4f46e5');
$lang     = setting('i18n.default_locale', getenv('DEFAULT_LOCALE') ?: 'tr');
$enabledLocales = json_decode(setting('i18n.enabled_locales', getenv('ENABLED_LOCALES') ?: '["tr","en"]'), true) ?: [];
$canonical = base_url(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
if (strpos($canonical, 'http') !== 0) {
    $canonical = current_url();
}
$ogImage = setting('site.logo_url');
$customHeadCss = setting('custom.head_css');
$customHeadJs  = setting('custom.head_js');
$customBodyCss = setting('custom.body_css');
$customBodyJs  = setting('custom.body_js');
$headerAd      = setting('ads.header');
$sidebarAd     = setting('ads.sidebar');
$inlineAd      = setting('ads.inline');
$footerAd      = setting('ads.footer');
?>
<!doctype html>
<html lang="<?= h($lang) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= isset($title)? h($title).' - ' : '' ?><?= h($siteName) ?></title>
<meta name="description" content="<?= h($metaDesc) ?>">
<link rel="canonical" href="<?= h($canonical) ?>">
<meta property="og:title" content="<?= isset($title)? h($title).' - ' : '' ?><?= h($siteName) ?>">
<meta property="og:description" content="<?= h($metaDesc) ?>">
<meta property="og:url" content="<?= h(current_url()) ?>">
<meta property="og:site_name" content="<?= h($siteName) ?>">
<?php if($ogImage): ?>
<meta property="og:image" content="<?= h($ogImage) ?>">
<?php endif; ?>
<?php foreach ($enabledLocales as $locale): ?>
<link rel="alternate" href="<?= h($canonical) ?>" hreflang="<?= h($locale) ?>">
<?php endforeach; ?>
<link rel="manifest" href="/manifest.webmanifest">
<meta name="theme-color" content="<?= h($primary) ?>">

<!-- Tailwind CDN (prefix tw-) -->
<script>
  tailwind = { config: { prefix: 'tw-' } };
</script>
<script src="https://cdn.tailwindcss.com"></script>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<link rel="stylesheet" href="/assets/css/app.css">
<style>
:root { --primary: <?= h($primary) ?>; }
.tw-btn-primary{ background: var(--primary); color:#fff; }
</style>

<?= $customHeadCss ?>
<?= $customHeadJs ?>
</head>
<body class="tw-bg-gray-50">
<header class="tw-bg-white tw-border-b">
  <div class="tw-max-w-6xl tw-mx-auto tw-px-4 tw-py-3 tw-flex tw-flex-col md:tw-flex-row md:tw-items-center md:tw-justify-between tw-gap-3">
    <a href="/" class="tw-font-semibold tw-text-lg"><?= h($siteName) ?></a>
    <nav class="tw-flex tw-flex-wrap tw-gap-3 tw-text-sm">
      <a href="/new" class="tw-text-gray-600 hover:tw-text-gray-900">Yeni</a>
      <a href="/terms" class="tw-text-gray-600 hover:tw-text-gray-900">Terms</a>
      <a href="/privacy" class="tw-text-gray-600 hover:tw-text-gray-900">Privacy</a>
      <a href="/advertising" class="tw-text-gray-600 hover:tw-text-gray-900">Advertising</a>
      <a href="/contact" class="tw-text-gray-600 hover:tw-text-gray-900">Contact</a>
    </nav>
  </div>
  <?php if($headerAd): ?>
  <div class="tw-border-t tw-bg-slate-50">
    <div class="tw-max-w-6xl tw-mx-auto tw-px-4 tw-py-2 tw-text-center">
      <?= $headerAd ?>
    </div>
  </div>
  <?php endif; ?>
</header>

<main class="tw-max-w-6xl tw-mx-auto tw-p-4">
  <div class="lg:tw-grid lg:tw-grid-cols-[minmax(0,1fr)_280px] lg:tw-gap-6">
    <div class="tw-space-y-4">
      <section id="installPrompt" class="tw-hidden tw-bg-white tw-border tw-rounded-2xl tw-p-4 tw-flex tw-flex-col md:tw-flex-row md:tw-items-center md:tw-justify-between tw-gap-3">
        <div>
          <h2 class="tw-text-base tw-font-semibold tw-text-gray-900">SolveClone&rsquo;u ana ekrana ekleyin</h2>
          <p id="platformTip" class="tw-text-sm tw-text-gray-600">Çevrimdışı kullanmak için cihazınıza yükleyin.</p>
        </div>
        <button type="button" class="btn btn-primary tw-self-start md:tw-self-center">Yükle</button>
      </section>
      <?php if (!empty($inlineAd)): ?>
        <div class="tw-text-center">
          <?= $inlineAd ?>
        </div>
      <?php endif; ?>
      <?php if (isset($content)) echo $content; else { if (isset($yield)) call_user_func($yield); } ?>
    </div>
    <?php if($sidebarAd): ?>
      <aside class="tw-mt-6 lg:tw-mt-0">
        <div class="tw-sticky tw-top-4 tw-space-y-4">
          <?= $sidebarAd ?>
        </div>
      </aside>
    <?php endif; ?>
  </div>
</main>

<footer class="tw-border-t tw-bg-white tw-mt-10">
  <div class="tw-max-w-6xl tw-mx-auto tw-px-4 tw-py-6 tw-text-sm tw-text-gray-600 tw-space-y-4">
    <?php if($footerAd): ?>
      <div class="tw-text-center"><?= $footerAd ?></div>
    <?php endif; ?>
    <div><?= setting('footer.html') ?: '&copy; '.date('Y').' '.h($siteName) ?></div>
  </div>
</footer>

<script src="/assets/js/app.js"></script>
<?= $customBodyCss ?>
<?= $customBodyJs ?>
</body></html>
