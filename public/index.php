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
$contact    = contact_settings($pdo);
$theme      = theme_settings($pdo);
$assets     = custom_assets($pdo);
$languages  = available_languages($pdo);
$recaptchaConfig = recaptcha_settings($pdo);
$pageTitle  = $site['title'] ?? t('title');
$canonical  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://')
            . ($_SERVER['HTTP_HOST'] ?? 'localhost')
            . strtok($_SERVER['REQUEST_URI'] ?? '/', '?');

$page = $_GET['p'] ?? 'home';
$allowed = ['home', 'book', 'date', 'time', 'confirm', 'done'];
if (!in_array($page, $allowed, true)) $page = 'home';
?><!doctype html>
<html lang="<?= h($langCode) ?>" class="scroll-smooth" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= h($pageTitle) ?> · v2.0</title>
  <meta name="description" content="<?= h($site['description'] ?? '') ?>">
  <?php if (!empty($site['meta_keywords'])): ?>
    <meta name="keywords" content="<?= h($site['meta_keywords']) ?>">
  <?php endif; ?>
  <link rel="canonical" href="<?= h($canonical) ?>">
  <meta property="og:title" content="<?= h($pageTitle) ?>">
  <meta property="og:description" content="<?= h($site['description'] ?? '') ?>">
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= h($canonical) ?>">
  <meta property="og:locale" content="<?= h($langCode) ?>">
  <meta name="theme-color" content="<?= h($theme['primary']) ?>">
  <script src="https://cdn.tailwindcss.com"></script>
  <?php if (!empty($recaptchaConfig['enabled']) && !empty($recaptchaConfig['site_key'])): ?>
    <script src="https://www.google.com/recaptcha/api.js?hl=<?= h($langCode) ?>" async defer></script>
  <?php endif; ?>
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
      transition: background-color .3s ease, color .3s ease;
    }
    .theme-surface { background-color: var(--color-surface); }
    .btn-primary {
      background-color: var(--color-primary);
      color: #fff;
    }
    .btn-primary:hover { filter: brightness(0.9); }
    [data-theme="dark"] body { background-color: #0f172a; color: #e2e8f0; }
    [data-theme="dark"] .theme-surface { background-color: rgba(15,23,42,0.85); }
    [data-theme="dark"] .shadow-xl { box-shadow: 0 25px 50px -12px rgba(15,23,42,.45); }
    [data-theme="dark"] .border-slate-200\/60 { border-color: rgba(148,163,184,.4); }
    [data-theme="dark"] .bg-slate-50 { background-color: rgba(148,163,184,0.08) !important; }
    [data-theme="dark"] .text-slate-500 { color: rgba(226,232,240,0.7) !important; }
    [data-theme="dark"] .text-slate-600 { color: rgba(226,232,240,0.85) !important; }
    [data-theme="dark"] .text-slate-700 { color: rgba(241,245,249,0.92) !important; }
    [data-theme="dark"] .border { border-color: rgba(148,163,184,0.35) !important; }
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
    <div class="relative max-w-7xl mx-auto px-4 py-8 text-white">
      <div class="flex items-center justify-between gap-6 flex-wrap">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center text-2xl">🗓️</div>
          <div>
            <h1 class="text-2xl md:text-3xl font-semibold"><?= h($site['title'] ?? t('title')) ?> <span class="text-white/70">v2.0</span></h1>
            <p class="mt-1 text-white/80 text-sm md:text-base leading-relaxed max-w-xl"><?= h($site['description'] ?? t('subtitle')) ?></p>
          </div>
        </div>
        <div class="flex items-center gap-3 text-sm flex-wrap justify-end">
          <?php if ($languages): ?>
            <div class="chip flex items-center gap-2" data-language-switch>
              <span><?= h(t('language.switch')) ?>:</span>
              <div class="flex items-center gap-1" role="group" aria-label="<?= h(t('language.switch')) ?>">
                <?php foreach ($languages as $code=>$lang): ?>
                  <button type="button" data-lang="<?= h($code) ?>" class="px-2 py-1 rounded-full border border-white/30 text-xs <?= $code === $langCode ? 'bg-white/90 text-slate-900' : 'bg-white/10 text-white hover:bg-white/20' ?>">
                    <?= h($lang['name']) ?>
                  </button>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
          <button type="button" class="chip flex items-center gap-2" id="themeToggle" aria-label="Tema değiştir">
            <span class="hidden md:inline">Tema</span>
            <span class="text-lg" data-theme-icon="light">🌙</span>
            <span class="text-lg hidden" data-theme-icon="dark">☀️</span>
          </button>
        </div>
      </div>
      <?php $hasSocial = array_filter($contact); if ($hasSocial): ?>
        <div class="mt-6 flex flex-wrap items-center gap-3 text-sm">
          <?php
            $socialIcons = [
              'phone' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h1.5a1.5 1.5 0 001.5-1.5v-2.382a1.5 1.5 0 00-1.318-1.488l-3.178-.318a1.5 1.5 0 00-1.317.75l-.845 1.408a12.035 12.035 0 01-5.294-5.294l1.408-.845a1.5 1.5 0 00.75-1.317L9.62 6.318A1.5 1.5 0 008.132 5H5.75A1.5 1.5 0 004.25 6.5v.25z"/></svg>',
              'whatsapp' => '<svg class="w-4 h-4" viewBox="0 0 32 32" aria-hidden="true"><path fill="currentColor" d="M16.02 2.4C8.84 2.4 3 8.24 3 15.42c0 2.7.79 5.21 2.3 7.4L3 29l6.36-2.33c2.07 1.13 4.42 1.73 6.67 1.73 7.18 0 13.02-5.84 13.02-13.02 0-7.18-5.84-12.98-13.02-12.98zm7.66 18.7c-.34.96-1.97 1.82-2.73 1.9-.7.07-1.6.1-2.59-.16-.6-.15-1.37-.45-2.37-.88-4.18-1.8-6.87-6.2-7.08-6.49-.2-.3-1.7-2.27-1.7-4.34 0-2.07 1.1-3.08 1.48-3.5.38-.4.83-.5 1.1-.5.27 0 .54 0 .78.02.25.02.6-.1.94.72.34.82 1.15 2.82 1.26 3.02.1.2.17.45.03.72-.13.27-.2.45-.4.7-.2.24-.42.54-.6.72-.2.18-.4.38-.17.76.23.38 1.02 1.68 2.2 2.72 1.52 1.36 2.8 1.78 3.18 1.98.4.2.62.17.85-.1.23-.28.98-1.14 1.25-1.53.27-.38.52-.32.87-.2.35.13 2.2 1.03 2.57 1.22.38.18.63.27.72.42.1.14.1 1-.23 1.96z"/></svg>',
              'instagram' => '<svg class="w-4 h-4" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M7 2h10a5 5 0 015 5v10a5 5 0 01-5 5H7a5 5 0 01-5-5V7a5 5 0 015-5zm0 2a3 3 0 00-3 3v10a3 3 0 003 3h10a3 3 0 003-3V7a3 3 0 00-3-3H7zm5 3.5A4.5 4.5 0 1112 18.5 4.5 4.5 0 0112 7.5zm0 2A2.5 2.5 0 1014.5 12 2.5 2.5 0 0012 9.5zm6.75-.25a1.25 1.25 0 11-1.25-1.25 1.25 1.25 0 011.25 1.25z"/></svg>',
              'facebook' => '<svg class="w-4 h-4" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M13 22V12h3l.5-4H13V6.2c0-1.1.3-1.8 1.9-1.8H17V1h-2.6C11.4 1 10 2.7 10 5.6V8H7v4h3v10h3z"/></svg>',
              'twitter' => '<svg class="w-4 h-4" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M8.29 20c7.55 0 11.68-6.26 11.68-11.68 0-.18 0-.35-.01-.53A8.35 8.35 0 0022 5.92a8.2 8.2 0 01-2.36.65 4.12 4.12 0 001.8-2.27 8.24 8.24 0 01-2.6.99 4.11 4.11 0 00-7 3.75A11.66 11.66 0 013 4.9a4.1 4.1 0 001.27 5.48 4.07 4.07 0 01-1.86-.52v.05a4.11 4.11 0 003.3 4.03 4.15 4.15 0 01-1.85.07 4.12 4.12 0 003.84 2.85A8.25 8.25 0 012 18.1 11.64 11.64 0 008.29 20z"/></svg>',
              'tiktok' => '<svg class="w-4 h-4" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M21 7.53a5.73 5.73 0 01-3.29-1.04A6.53 6.53 0 0115.6 3H13v13.3a2.36 2.36 0 01-4.72 0 2.36 2.36 0 012.36-2.35 2.4 2.4 0 01.69.11V11a5.35 5.35 0 00-.69-.05 5.36 5.36 0 105.36 5.35V9.89a7.51 7.51 0 003.29.76V7.53z"/></svg>',
              'linkedin' => '<svg class="w-4 h-4" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M20.45 20.45h-3.55v-5.57c0-1.33-.02-3.03-1.85-3.03-1.85 0-2.13 1.45-2.13 2.94v5.66H9.37V9h3.41v1.56h.05c.47-.89 1.62-1.83 3.33-1.83 3.56 0 4.22 2.34 4.22 5.39v6.33zM5.34 7.43a2.07 2.07 0 112.07-2.07 2.07 2.07 0 01-2.07 2.07zM7.12 20.45H3.56V9h3.56z"/></svg>',
            ];
            $socialLabels = [
              'phone' => 'Telefon',
              'whatsapp' => 'WhatsApp',
              'instagram' => 'Instagram',
              'facebook' => 'Facebook',
              'twitter' => 'Twitter',
              'tiktok' => 'TikTok',
              'linkedin' => 'LinkedIn',
            ];
            foreach ($contact as $key => $value):
              if (empty($value)) continue;
              $href = '#';
              $targetAttr = ' target="_blank" rel="noopener"';
              if ($key === 'phone') {
                $href = 'tel:'.preg_replace('/[^0-9+]/', '', $value);
                $targetAttr = '';
              } elseif ($key === 'whatsapp') {
                $href = 'https://wa.me/'.preg_replace('/[^0-9]/', '', $value);
              } else {
                $base = [
                  'instagram' => 'https://instagram.com/',
                  'facebook'  => 'https://facebook.com/',
                  'twitter'   => 'https://twitter.com/',
                  'tiktok'    => 'https://tiktok.com/@',
                  'linkedin'  => 'https://linkedin.com/in/',
                ][$key] ?? 'https://';
                $href = $base . ltrim($value, '@/');
              }
          ?>
              <a class="chip flex items-center gap-2 hover:bg-white/20 transition" href="<?= h($href) ?>"<?= $targetAttr ?> title="<?= h(($socialLabels[$key] ?? ucfirst($key)).': '.$value) ?>" aria-label="<?= h(($socialLabels[$key] ?? ucfirst($key)).': '.$value) ?>">
                <?= $socialIcons[$key] ?? '' ?>
                <span class="text-xs hidden sm:inline"><?= h($value) ?></span>
              </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
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

  <script>
    (function(){
      const root = document.documentElement;
      const storageKey = 'randevu-theme';
      const lightIcon = document.querySelector('[data-theme-icon="light"]');
      const darkIcon = document.querySelector('[data-theme-icon="dark"]');
      const applyTheme = (theme) => {
        root.setAttribute('data-theme', theme);
        if (lightIcon && darkIcon) {
          lightIcon.classList.toggle('hidden', theme !== 'light');
          darkIcon.classList.toggle('hidden', theme !== 'dark');
        }
      };
      const saved = localStorage.getItem(storageKey);
      const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      applyTheme(saved ? saved : (prefersDark ? 'dark' : 'light'));
      const toggle = document.getElementById('themeToggle');
      if (toggle) {
        toggle.addEventListener('click', () => {
          const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
          applyTheme(next);
          localStorage.setItem(storageKey, next);
        });
      }
      const langButtons = document.querySelectorAll('[data-language-switch] [data-lang]');
      if (langButtons.length) {
        const url = new URL(window.location.href);
        langButtons.forEach(btn => {
          btn.addEventListener('click', () => {
            url.searchParams.set('lang', btn.getAttribute('data-lang'));
            window.location.href = url.toString();
          });
        });
      }
      document.querySelectorAll('[data-phone-input]').forEach(input => {
        input.setAttribute('inputmode', 'numeric');
        input.setAttribute('maxlength', '12');
        const format = () => {
          let digits = input.value.replace(/\D/g, '').slice(0, 10);
          const parts = [];
          if (digits.length > 0) parts.push(digits.slice(0, Math.min(3, digits.length)));
          if (digits.length > 3) parts.push(digits.slice(3, Math.min(6, digits.length)));
          if (digits.length > 6) parts.push(digits.slice(6, 10));
          input.value = parts.join(' ').trim();
        };
        input.addEventListener('input', format);
        input.addEventListener('blur', format);
        format();
      });
    })();
  </script>
  <script type="application/ld+json">
  <?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'LocalBusiness',
    'name' => $site['title'] ?? 'Randevu Sistemi',
    'url' => $canonical,
    'description' => $site['description'] ?? '',
    'telephone' => $contact['phone'] ?? '',
  ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
  </script>
  <?php if (!empty($assets['js'])): ?>
  <script><?= $assets['js'] ?></script>
  <?php endif; ?>
</body>
</html>
