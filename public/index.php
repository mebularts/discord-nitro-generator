<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/notify.php';

$page = $_GET['p'] ?? 'home';
$allowed = ['home', 'book', 'date', 'time', 'confirm', 'done'];
if (!in_array($page, $allowed, true)) $page = 'home';
?><!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= h(t('title')) ?> · v2.0</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50">
  <section class="relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-b from-sky-600 to-indigo-700"></div>
    <div class="relative max-w-7xl mx-auto px-3 py-8 text-white">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-white/10 rounded-2xl flex items-center justify-center">🗓️</div>
        <h1 class="text-2xl md:text-3xl font-semibold">Randevu Sistemi <span class="text-white/70">v2.0</span></h1>
      </div>
      <p class="mt-1 text-white/80 text-sm md:text-base">Modern, hızlı ve mobil uyumlu.</p>
    </div>
  </section>

  <main class="max-w-7xl mx-auto px-4 mt-6">
    <div class="bg-white rounded-2xl shadow-xl border p-1">
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

  <footer class="py-10 text-center text-slate-500 text-sm">© 2025 v2.0</footer>
</body>
</html>
