<?php
declare(strict_types=1);
/** @var array $meta */
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($appLocale ?? 'en', ENT_QUOTES, 'UTF-8') ?>" class="tw-h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($meta['title'] ?? 'SolveClone', ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($meta['description'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:title" content="<?= htmlspecialchars($meta['title'] ?? 'SolveClone', ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($meta['description'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image" content="<?= htmlspecialchars($meta['image'] ?? app_icon_url(), ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:type" content="website">
    <meta name="theme-color" content="<?= htmlspecialchars(setting('theme_color', '#0ea5e9'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= app_icon_url('180x180') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= app_icon_url('32x32') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= app_icon_url('16x16') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.tailwindcss.com?plugins=line-clamp"></script>
    <script>tailwind.config = { prefix: 'tw-', theme: { extend: {} } };</script>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <?= setting('custom_head_html', '') ?>
</head>
<body class="tw-min-h-screen tw-bg-slate-950 tw-text-white">
<header class="tw-bg-gradient-to-r tw-from-sky-500 tw-to-purple-500 tw-py-6 tw-text-white">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between">
            <a class="navbar-brand tw-text-2xl tw-font-semibold tw-text-white" href="/"><?= htmlspecialchars($appName ?? 'SolveClone', ENT_QUOTES, 'UTF-8') ?></a>
            <div class="d-flex gap-2 align-items-center">
                <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#mobileMenu" aria-expanded="false" aria-label="<?= htmlspecialchars(__('layout.toggle_menu'), ENT_QUOTES, 'UTF-8') ?>">
                    ☰
                </button>
                <form method="get" action="" class="d-none d-md-flex align-items-center gap-2">
                    <label class="tw-text-xs tw-uppercase tw-tracking-wide"><?= htmlspecialchars(__('layout.language'), ENT_QUOTES, 'UTF-8') ?></label>
                    <select name="lang" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php foreach ($availableLocales as $localeOption): ?>
                            <option value="<?= htmlspecialchars($localeOption, ENT_QUOTES, 'UTF-8') ?>" <?= $localeOption === $appLocale ? 'selected' : '' ?>><?= strtoupper($localeOption) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>
        <div class="collapse" id="mobileMenu">
            <div class="tw-mt-4">
                <form method="get" action="" class="d-flex gap-2">
                    <select name="lang" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php foreach ($availableLocales as $localeOption): ?>
                            <option value="<?= htmlspecialchars($localeOption, ENT_QUOTES, 'UTF-8') ?>" <?= $localeOption === $appLocale ? 'selected' : '' ?>><?= strtoupper($localeOption) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>
    </div>
</header>
<main class="tw-py-8">
    <div class="container">
        <?= $content ?? '' ?>
    </div>
</main>
<footer class="tw-bg-slate-900 tw-border-t tw-border-slate-800 tw-py-6">
    <div class="container">
        <p class="tw-text-sm tw-text-slate-300 mb-1"><?= htmlspecialchars(__('footer.about'), ENT_QUOTES, 'UTF-8') ?></p>
        <p class="tw-text-sm tw-text-slate-500"><?= htmlspecialchars(__('footer.contact'), ENT_QUOTES, 'UTF-8') ?></p>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    window.app = { csrfToken: '<?= csrf_token() ?>' };
    window.translations = {
        showAnswer: '<?= addslashes(__('riddles.answer')) ?>',
        hideAnswer: '<?= addslashes(__('riddles.hide_answer')) ?>'
    };
</script>
<script src="<?= asset('js/app.js') ?>" defer></script>
<?= setting('custom_body_html', '') ?>
</body>
</html>
