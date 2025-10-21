<?php
use App\Security\CsrfTokenManager;
use App\Support\Helpers;
?>
<!DOCTYPE html>
<html lang="tr" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Helpers::escape($title ?? 'SolveClone'); ?></title>
    <link rel="manifest" href="/manifest.json">
    <link rel="stylesheet" href="<?= Helpers::asset('assets/css/app.css'); ?>">
    <meta name="theme-color" content="#0f172a">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a class="brand" href="/">SolveClone</a>
            <nav>
                <button class="theme-toggle" type="button" aria-label="Tema değiştir" data-theme-toggle>
                    <span class="icon">🌓</span>
                    <span>Temayı Değiştir</span>
                </button>
                <?php if (!empty($session['user'])): ?>
                    <a href="/notifications" class="nav-link">Bildirimler</a>
                    <a href="/settings" class="nav-link">Ayarlar</a>
                    <?php if (($session['user']['role'] ?? 'user') === 'admin'): ?>
                        <a href="/admin" class="nav-link">Yönetim</a>
                    <?php endif; ?>
                    <form method="post" action="/logout" class="inline-form">
                        <input type="hidden" name="_token" value="<?= CsrfTokenManager::token(); ?>">
                        <button type="submit" class="nav-link">Çıkış</button>
                    </form>
                <?php else: ?>
                    <a href="/login" class="nav-link">Giriş</a>
                    <a href="/register" class="nav-link nav-link-primary">Kayıt ol</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main class="container" tabindex="-1">
        <?php if (!empty($error)): ?>
            <div class="alert alert-error" role="alert"><?= Helpers::escape($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success" role="status"><?= Helpers::escape($success); ?></div>
        <?php endif; ?>
        <?= $content ?? ''; ?>
    </main>
    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?= date('Y'); ?> SolveClone. Toplulukla paylaş, ilham ol.</p>
        </div>
    </footer>
    <script src="<?= Helpers::asset('assets/js/app.js'); ?>" defer></script>
</body>
</html>
