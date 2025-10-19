<?php
require_once __DIR__.'/bootstrap.php';
must_login(['settings']);

$site    = site_settings($pdo);
$theme   = theme_settings($pdo);
$booking = booking_settings($pdo);
$assets  = custom_assets($pdo);

if (is_post()) {
  csrf_check();
  $action = $_POST['action'] ?? '';
  if ($action === 'site') {
    $site['title'] = trim($_POST['title'] ?? $site['title']);
    $site['description'] = trim($_POST['description'] ?? $site['description']);
    $site['footer'] = trim($_POST['footer'] ?? $site['footer']);
    settings_set($pdo, 'site', $site);
    set_flash('success', 'Site bilgileri güncellendi.');
  } elseif ($action === 'theme') {
    $theme['primary']   = $_POST['primary'] ?? $theme['primary'];
    $theme['secondary'] = $_POST['secondary'] ?? $theme['secondary'];
    $theme['surface']   = $_POST['surface'] ?? $theme['surface'];
    $theme['background']= $_POST['background'] ?? $theme['background'];
    $theme['text']      = $_POST['text'] ?? $theme['text'];
    settings_set($pdo, 'theme', $theme);
    set_flash('success', 'Tema ayarları güncellendi.');
  } elseif ($action === 'booking') {
    $booking['slot_minutes'] = max(5, (int)($_POST['slot_minutes'] ?? $booking['slot_minutes']));
    $booking['lead_days']    = max(0, (int)($_POST['lead_days'] ?? $booking['lead_days']));
    $booking['max_days']     = max($booking['lead_days'], (int)($_POST['max_days'] ?? $booking['max_days']));
    $booking['allow_weekend']= isset($_POST['allow_weekend']) ? 1 : 0;
    settings_set($pdo, 'booking', $booking);
    set_flash('success', 'Randevu ayarları güncellendi.');
  } elseif ($action === 'assets') {
    $assets['css'] = $_POST['css'] ?? $assets['css'];
    $assets['js']  = $_POST['js'] ?? $assets['js'];
    settings_set($pdo, 'custom_assets', $assets);
    set_flash('success', 'Özel kodlar güncellendi.');
  }
  redirect('/admin/settings.php');
}

admin_render_header('Site Ayarları', 'settings');
?>
<div class="space-y-6">
  <section class="border rounded-xl bg-white p-5">
    <h2 class="text-lg font-semibold text-slate-700 mb-3">Site Bilgileri</h2>
    <form method="post" class="grid md:grid-cols-2 gap-4">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="site">
      <label class="text-sm text-slate-600">Başlık
        <input type="text" name="title" value="<?= h($site['title']) ?>" class="mt-1 border rounded-lg w-full p-2">
      </label>
      <label class="text-sm text-slate-600">Kısa Açıklama
        <input type="text" name="description" value="<?= h($site['description']) ?>" class="mt-1 border rounded-lg w-full p-2">
      </label>
      <label class="text-sm text-slate-600 md:col-span-2">Footer
        <input type="text" name="footer" value="<?= h($site['footer']) ?>" class="mt-1 border rounded-lg w-full p-2">
      </label>
      <div class="md:col-span-2">
        <button class="px-4 py-2 rounded-lg bg-slate-900 text-white">Kaydet</button>
      </div>
    </form>
  </section>

  <section class="border rounded-xl bg-white p-5">
    <h2 class="text-lg font-semibold text-slate-700 mb-3">Tema Renkleri</h2>
    <form method="post" class="grid md:grid-cols-3 gap-4">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="theme">
      <label class="text-sm text-slate-600">Birincil
        <input type="color" name="primary" value="<?= h($theme['primary']) ?>" class="mt-1 w-16 h-10 border rounded">
      </label>
      <label class="text-sm text-slate-600">İkincil
        <input type="color" name="secondary" value="<?= h($theme['secondary']) ?>" class="mt-1 w-16 h-10 border rounded">
      </label>
      <label class="text-sm text-slate-600">Yüzey
        <input type="color" name="surface" value="<?= h($theme['surface']) ?>" class="mt-1 w-16 h-10 border rounded">
      </label>
      <label class="text-sm text-slate-600">Arkaplan
        <input type="color" name="background" value="<?= h($theme['background']) ?>" class="mt-1 w-16 h-10 border rounded">
      </label>
      <label class="text-sm text-slate-600">Metin
        <input type="color" name="text" value="<?= h($theme['text']) ?>" class="mt-1 w-16 h-10 border rounded">
      </label>
      <div class="md:col-span-3">
        <button class="px-4 py-2 rounded-lg bg-slate-900 text-white">Kaydet</button>
      </div>
    </form>
  </section>

  <section class="border rounded-xl bg-white p-5">
    <h2 class="text-lg font-semibold text-slate-700 mb-3">Randevu Ayarları</h2>
    <form method="post" class="grid md:grid-cols-2 gap-4">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="booking">
      <label class="text-sm text-slate-600">Slot Süresi (dk)
        <input type="number" name="slot_minutes" value="<?= h((string)$booking['slot_minutes']) ?>" class="mt-1 border rounded-lg w-full p-2">
      </label>
      <label class="text-sm text-slate-600">Önceden Rezervasyon (gün)
        <input type="number" name="lead_days" value="<?= h((string)$booking['lead_days']) ?>" class="mt-1 border rounded-lg w-full p-2">
      </label>
      <label class="text-sm text-slate-600">Maksimum İleri Tarih (gün)
        <input type="number" name="max_days" value="<?= h((string)$booking['max_days']) ?>" class="mt-1 border rounded-lg w-full p-2">
      </label>
      <label class="text-sm text-slate-600">
        <input type="checkbox" name="allow_weekend" value="1" <?= !empty($booking['allow_weekend']) ? 'checked' : '' ?>> Hafta sonu randevu olsun
      </label>
      <div class="md:col-span-2">
        <button class="px-4 py-2 rounded-lg bg-slate-900 text-white">Kaydet</button>
      </div>
    </form>
  </section>

  <section class="border rounded-xl bg-white p-5">
    <h2 class="text-lg font-semibold text-slate-700 mb-3">Özel CSS & JS</h2>
    <form method="post" class="space-y-4">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="assets">
      <div>
        <label class="text-sm text-slate-600">Custom CSS</label>
        <textarea name="css" rows="6" class="mt-1 border rounded-lg w-full p-2 font-mono text-xs"><?= h($assets['css']) ?></textarea>
      </div>
      <div>
        <label class="text-sm text-slate-600">Custom JS</label>
        <textarea name="js" rows="6" class="mt-1 border rounded-lg w-full p-2 font-mono text-xs"><?= h($assets['js']) ?></textarea>
      </div>
      <button class="px-4 py-2 rounded-lg bg-slate-900 text-white">Kaydet</button>
    </form>
  </section>
</div>
<?php
admin_render_footer();
