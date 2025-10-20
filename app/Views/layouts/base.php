
<?php
$siteName = 'SolveClone';
$metaDesc = getenv('SEO_META_DESCRIPTION') ?: 'Modern, çok dilli bulmaca platformu.';
$primary  = getenv('THEME_PRIMARY') ?: '#4f46e5';
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= isset($title)? h($title).' - ' : '' ?><?= h($siteName) ?></title>
<meta name="description" content="<?= h($metaDesc) ?>">
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

<?= getenv('custom.head_css') ?: '' ?>
<?= getenv('custom.head_js') ?: '' ?>
</head>
<body class="tw-bg-gray-50">
<header class="tw-bg-white tw-border-b">
  <div class="tw-max-w-6xl tw-mx-auto tw-px-4 tw-py-3 tw-flex tw-items-center tw-justify-between">
    <a href="/" class="tw-font-semibold"><?= h($siteName) ?></a>
    <nav class="tw-flex tw-gap-3 tw-text-sm">
      <a href="/new" class="tw-text-gray-600 hover:tw-text-gray-900">Yeni</a>
      <a href="/terms" class="tw-text-gray-600 hover:tw-text-gray-900">Terms</a>
      <a href="/privacy" class="tw-text-gray-600 hover:tw-text-gray-900">Privacy</a>
      <a href="/advertising" class="tw-text-gray-600 hover:tw-text-gray-900">Advertising</a>
      <a href="/contact" class="tw-text-gray-600 hover:tw-text-gray-900">Contact</a>
    </nav>
  </div>
</header>

<main class="tw-max-w-6xl tw-mx-auto tw-p-4">
  <?php if (isset($content)) echo $content; else { if (isset($yield)) call_user_func($yield); } ?>
</main>

<footer class="tw-border-t tw-bg-white tw-mt-10">
  <div class="tw-max-w-6xl tw-mx-auto tw-px-4 tw-py-6 tw-text-sm tw-text-gray-600">
    <?= getenv('footer.html') ?: '&copy; '.date('Y').' SolveClone' ?>
  </div>
</footer>

<script src="/assets/js/app.js"></script>
<?= getenv('custom.body_css') ?: '' ?>
<?= getenv('custom.body_js') ?: '' ?>
</body></html>
