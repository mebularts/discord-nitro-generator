<?php
require_once __DIR__.'/bootstrap.php';
must_login(['settings']);

$site      = site_settings($pdo);
$contact   = contact_settings($pdo);
$theme     = theme_settings($pdo);
$booking   = booking_settings($pdo);
$assets    = custom_assets($pdo);
$smtp      = smtp_settings($pdo);
$sms       = sms_settings($pdo);
$templates = notification_templates($pdo);
$security  = recaptcha_settings($pdo);

if (is_post()) {
  csrf_check();
  $action = $_POST['action'] ?? '';
  if ($action === 'site') {
    $site['title'] = trim($_POST['title'] ?? $site['title']);
    $site['description'] = trim($_POST['description'] ?? $site['description']);
    $site['footer'] = trim($_POST['footer'] ?? $site['footer']);
    $site['meta_keywords'] = trim($_POST['meta_keywords'] ?? ($site['meta_keywords'] ?? ''));
    settings_set($pdo, 'site', $site);
    set_flash('success', 'Site bilgileri güncellendi.');
  } elseif ($action === 'contact') {
    foreach ($contact as $key => $_) {
      $contact[$key] = trim($_POST[$key] ?? $contact[$key]);
    }
    settings_set($pdo, 'contact', $contact);
    set_flash('success', 'İletişim ve sosyal medya bilgileri güncellendi.');
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
  } elseif ($action === 'smtp') {
    $smtp['enabled']    = isset($_POST['enabled']) ? 1 : 0;
    $smtp['host']       = trim($_POST['host'] ?? $smtp['host']);
    $smtp['port']       = (int)($_POST['port'] ?? $smtp['port']);
    $smtp['encryption'] = in_array($_POST['encryption'] ?? $smtp['encryption'], ['tls','ssl','none'], true) ? $_POST['encryption'] : $smtp['encryption'];
    $smtp['username']   = trim($_POST['username'] ?? $smtp['username']);
    if (!empty($_POST['password'])) {
      $smtp['password'] = $_POST['password'];
    }
    $smtp['from']       = trim($_POST['from'] ?? $smtp['from']);
    $smtp['from_name']  = trim($_POST['from_name'] ?? $smtp['from_name']);
    settings_set($pdo, 'smtp', $smtp);
    set_flash('success', 'SMTP ayarları güncellendi.');
  } elseif ($action === 'sms') {
    $sms['provider'] = $_POST['provider'] ?? $sms['provider'];
    $sms['username'] = trim($_POST['username'] ?? $sms['username']);
    if (!empty($_POST['password'])) {
      $sms['password'] = $_POST['password'];
    }
    $sms['header']   = trim($_POST['header'] ?? $sms['header']);
    settings_set($pdo, 'sms', $sms);
    set_flash('success', 'SMS ayarları güncellendi.');
  } elseif ($action === 'security') {
    $security['enabled'] = isset($_POST['enabled']) ? 1 : 0;
    $security['site_key'] = trim($_POST['site_key'] ?? $security['site_key']);
    if (!empty($_POST['secret_key'])) {
      $security['secret_key'] = trim($_POST['secret_key']);
    }
    settings_set($pdo, 'recaptcha', $security);
    set_flash('success', 'Güvenlik ayarları güncellendi.');
  } elseif ($action === 'templates') {
    foreach (['email','sms'] as $channel) {
      foreach ($templates[$channel] as $key => $value) {
        if ($channel === 'email') {
          $templates['email'][$key]['subject'] = $_POST['email'][$key]['subject'] ?? $value['subject'];
          $templates['email'][$key]['body']    = $_POST['email'][$key]['body'] ?? $value['body'];
        } else {
          $templates['sms'][$key] = $_POST['sms'][$key] ?? $value;
        }
      }
    }
    settings_set($pdo, 'notification_templates', $templates);
    set_flash('success', 'Bildirim şablonları güncellendi.');
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
      <label class="text-sm text-slate-600 md:col-span-2">Meta Anahtar Kelimeler
        <input type="text" name="meta_keywords" value="<?= h($site['meta_keywords'] ?? '') ?>" class="mt-1 border rounded-lg w-full p-2" placeholder="randevu, danışmanlık, hizmet">
      </label>
      <div class="md:col-span-2">
        <button class="px-4 py-2 rounded-lg bg-slate-900 text-white">Kaydet</button>
      </div>
    </form>
  </section>

  <section class="border rounded-xl bg-white p-5">
    <h2 class="text-lg font-semibold text-slate-700 mb-3">İletişim & Sosyal Medya</h2>
    <form method="post" class="grid md:grid-cols-3 gap-4">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="contact">
      <label class="text-sm text-slate-600">Telefon
        <input type="text" name="phone" value="<?= h($contact['phone']) ?>" class="mt-1 border rounded-lg w-full p-2" placeholder="0 (5xx) xxx xx xx">
      </label>
      <label class="text-sm text-slate-600">WhatsApp
        <input type="text" name="whatsapp" value="<?= h($contact['whatsapp']) ?>" class="mt-1 border rounded-lg w-full p-2" placeholder="5xxxxxxxxx">
      </label>
      <label class="text-sm text-slate-600">Instagram
        <input type="text" name="instagram" value="<?= h($contact['instagram']) ?>" class="mt-1 border rounded-lg w-full p-2" placeholder="kullaniciadi">
      </label>
      <label class="text-sm text-slate-600">Facebook
        <input type="text" name="facebook" value="<?= h($contact['facebook']) ?>" class="mt-1 border rounded-lg w-full p-2">
      </label>
      <label class="text-sm text-slate-600">Twitter / X
        <input type="text" name="twitter" value="<?= h($contact['twitter']) ?>" class="mt-1 border rounded-lg w-full p-2">
      </label>
      <label class="text-sm text-slate-600">TikTok
        <input type="text" name="tiktok" value="<?= h($contact['tiktok']) ?>" class="mt-1 border rounded-lg w-full p-2">
      </label>
      <label class="text-sm text-slate-600">LinkedIn
        <input type="text" name="linkedin" value="<?= h($contact['linkedin']) ?>" class="mt-1 border rounded-lg w-full p-2">
      </label>
      <div class="md:col-span-3">
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
    <h2 class="text-lg font-semibold text-slate-700 mb-3">Güvenlik & reCAPTCHA</h2>
    <form method="post" class="grid md:grid-cols-2 gap-4">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="security">
      <label class="text-sm text-slate-600 flex items-center gap-2">
        <input type="checkbox" name="enabled" value="1" <?= !empty($security['enabled']) ? 'checked' : '' ?>> Etkin
      </label>
      <div></div>
      <label class="text-sm text-slate-600">Site Key
        <input type="text" name="site_key" value="<?= h($security['site_key']) ?>" class="mt-1 border rounded-lg w-full p-2" placeholder="reCAPTCHA site key">
      </label>
      <label class="text-sm text-slate-600">Secret Key
        <input type="password" name="secret_key" value="" class="mt-1 border rounded-lg w-full p-2" placeholder="Yeni secret key">
        <p class="text-xs text-slate-400 mt-1">Boş bırakırsanız mevcut anahtar korunur.</p>
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

  <section class="border rounded-xl bg-white p-5">
    <h2 class="text-lg font-semibold text-slate-700 mb-3">SMTP (E-posta)</h2>
    <form method="post" class="grid md:grid-cols-2 gap-4">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="smtp">
      <label class="text-sm text-slate-600 flex items-center gap-2">
        <input type="checkbox" name="enabled" value="1" <?= !empty($smtp['enabled']) ? 'checked' : '' ?>> Etkin
      </label>
      <div></div>
      <label class="text-sm text-slate-600">Sunucu (Host)
        <input type="text" name="host" value="<?= h($smtp['host']) ?>" class="mt-1 border rounded-lg w-full p-2">
      </label>
      <label class="text-sm text-slate-600">Port
        <input type="number" name="port" value="<?= h((string)$smtp['port']) ?>" class="mt-1 border rounded-lg w-full p-2">
      </label>
      <label class="text-sm text-slate-600">Şifreleme
        <select name="encryption" class="mt-1 border rounded-lg w-full p-2">
          <option value="tls" <?= ($smtp['encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS</option>
          <option value="ssl" <?= ($smtp['encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
          <option value="none" <?= ($smtp['encryption'] ?? '') === 'none' ? 'selected' : '' ?>>Şifreleme Yok</option>
        </select>
      </label>
      <label class="text-sm text-slate-600">Kullanıcı Adı
        <input type="text" name="username" value="<?= h($smtp['username']) ?>" class="mt-1 border rounded-lg w-full p-2">
      </label>
      <label class="text-sm text-slate-600">Şifre (değiştirmek için girin)
        <input type="password" name="password" class="mt-1 border rounded-lg w-full p-2" autocomplete="new-password">
      </label>
      <label class="text-sm text-slate-600">Gönderen E-posta
        <input type="email" name="from" value="<?= h($smtp['from']) ?>" class="mt-1 border rounded-lg w-full p-2">
      </label>
      <label class="text-sm text-slate-600">Gönderen Adı
        <input type="text" name="from_name" value="<?= h($smtp['from_name']) ?>" class="mt-1 border rounded-lg w-full p-2">
      </label>
      <div class="md:col-span-2">
        <button class="px-4 py-2 rounded-lg bg-slate-900 text-white">Kaydet</button>
      </div>
    </form>
  </section>

  <section class="border rounded-xl bg-white p-5">
    <h2 class="text-lg font-semibold text-slate-700 mb-3">SMS Sağlayıcı</h2>
    <form method="post" class="grid md:grid-cols-2 gap-4">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="sms">
      <label class="text-sm text-slate-600">Sağlayıcı
        <select name="provider" class="mt-1 border rounded-lg w-full p-2">
          <option value="netgsm" <?= ($sms['provider'] ?? '') === 'netgsm' ? 'selected' : '' ?>>NetGSM</option>
          <option value="iletimerkezi" <?= ($sms['provider'] ?? '') === 'iletimerkezi' ? 'selected' : '' ?>>İleti Merkezi</option>
        </select>
      </label>
      <div></div>
      <label class="text-sm text-slate-600">Kullanıcı Adı
        <input type="text" name="username" value="<?= h($sms['username']) ?>" class="mt-1 border rounded-lg w-full p-2">
      </label>
      <label class="text-sm text-slate-600">Şifre / API Key (değiştirmek için girin)
        <input type="password" name="password" class="mt-1 border rounded-lg w-full p-2" autocomplete="new-password">
      </label>
      <label class="text-sm text-slate-600">Başlık (Originator)
        <input type="text" name="header" value="<?= h($sms['header']) ?>" class="mt-1 border rounded-lg w-full p-2">
      </label>
      <div class="md:col-span-2">
        <button class="px-4 py-2 rounded-lg bg-slate-900 text-white">Kaydet</button>
      </div>
    </form>
  </section>

  <section class="border rounded-xl bg-white p-5">
    <h2 class="text-lg font-semibold text-slate-700 mb-3">Bildirim Şablonları</h2>
    <form method="post" class="space-y-6">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="templates">
      <div class="grid md:grid-cols-2 gap-6">
        <?php foreach ($templates['email'] as $key => $tpl): ?>
          <div class="border rounded-lg p-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-2">E-posta · <?= h(strtoupper(str_replace('_',' ', $key))) ?></h3>
            <label class="block text-xs text-slate-500 mb-2">Konu
              <input type="text" name="email[<?= h($key) ?>][subject]" value="<?= h($tpl['subject']) ?>" class="mt-1 border rounded w-full p-2">
            </label>
            <label class="block text-xs text-slate-500">İçerik (HTML destekler)
              <textarea name="email[<?= h($key) ?>][body]" rows="4" class="mt-1 border rounded w-full p-2 font-mono text-xs"><?= h($tpl['body']) ?></textarea>
            </label>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="grid md:grid-cols-3 gap-6">
        <?php foreach ($templates['sms'] as $key => $tpl): ?>
          <div class="border rounded-lg p-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-2">SMS · <?= h(strtoupper(str_replace('_',' ', $key))) ?></h3>
            <textarea name="sms[<?= h($key) ?>]" rows="4" class="mt-1 border rounded w-full p-2 text-sm"><?= h($tpl) ?></textarea>
          </div>
        <?php endforeach; ?>
      </div>
      <div>
        <p class="text-xs text-slate-500">Kullanılabilir değişkenler: {appointment_id}, {customer_name}, {provider_name}, {appointment_date}, {appointment_time}, {message}</p>
      </div>
      <button class="px-4 py-2 rounded-lg bg-slate-900 text-white">Kaydet</button>
    </form>
  </section>
</div>
<?php
admin_render_footer();
