<?php
require_once __DIR__.'/bootstrap.php';
must_login(['customers']);

$key = trim((string)($_GET['key'] ?? ''));
if ($key === '') {
  redirect('/admin/customers.php');
}

$customer = q($pdo, 'SELECT COALESCE(NULLIF(email, ""), phone) AS customer_key, MAX(full_name) AS full_name, MAX(phone) AS phone, MAX(email) AS email, COUNT(*) AS total_count
                    FROM appointments WHERE COALESCE(NULLIF(email, ""), phone) = ? LIMIT 1', [$key])->fetch();
if (!$customer) {
  set_flash('error', 'Müşteri bulunamadı.');
  redirect('/admin/customers.php');
}

$history = customer_history($pdo, $key);
$latest  = $history[0] ?? null;
$templates = notification_templates($pdo);
$log = [];

if (is_post()) {
  csrf_check();
  $action = $_POST['action'] ?? '';
  if ($action === 'quick_email') {
    $subjectTpl = $_POST['subject'] ?? ($templates['email']['bulk_default']['subject'] ?? 'Bilgilendirme');
    $bodyTpl    = $_POST['body'] ?? ($templates['email']['bulk_default']['body'] ?? '{message}');
    $context = [
      'appointment_id'   => $latest['id'] ?? '-',
      'customer_name'    => $customer['full_name'] ?? '',
      'provider_name'    => $latest['provider_name'] ?? '',
      'appointment_date' => isset($latest['app_date']) ? date('d.m.Y', strtotime($latest['app_date'])) : '',
      'appointment_time' => $latest['app_time'] ?? '',
      'message'          => trim((string)($_POST['message'] ?? '')),
    ];
    if (!empty($customer['email'])) {
      $subject = format_notification($subjectTpl, $context);
      $body    = format_notification($bodyTpl, $context);
      $ok = send_email($pdo, $customer['email'], $subject, $body);
      $log[] = ($ok ? '✅' : '⚠️').' '.$customer['email'].' · '.$subject;
      set_flash($ok ? 'success' : 'error', $ok ? 'E-posta gönderildi.' : 'E-posta gönderilemedi.');
    } else {
      set_flash('error', 'Kayıtlı e-posta bulunamadı.');
    }
    $_SESSION['customer_log'] = $log;
    redirect('/admin/customer.php?key='.urlencode($key));
  } elseif ($action === 'quick_sms') {
    $context = [
      'appointment_id'   => $latest['id'] ?? '-',
      'customer_name'    => $customer['full_name'] ?? '',
      'provider_name'    => $latest['provider_name'] ?? '',
      'appointment_date' => isset($latest['app_date']) ? date('d.m.Y', strtotime($latest['app_date'])) : '',
      'appointment_time' => $latest['app_time'] ?? '',
      'message'          => trim((string)($_POST['message'] ?? '')),
    ];
    if (!empty($customer['phone'])) {
      $tpl = $_POST['sms_template'] ?? ($templates['sms']['bulk_default'] ?? '{message}');
      $content = format_notification($tpl, $context);
      $resp = send_sms($pdo, $customer['phone'], $content);
      $log[] = ($resp['ok'] ? '✅' : '⚠️').' '.$customer['phone'];
      set_flash(!empty($resp['ok']) ? 'success' : 'error', !empty($resp['ok']) ? 'SMS gönderildi.' : 'SMS gönderilemedi.');
    } else {
      set_flash('error', 'Kayıtlı telefon bulunamadı.');
    }
    $_SESSION['customer_log'] = $log;
    redirect('/admin/customer.php?key='.urlencode($key));
  }
}

if (isset($_SESSION['customer_log'])) {
  $log = $_SESSION['customer_log'];
  unset($_SESSION['customer_log']);
}

admin_render_header('Müşteri Detayı', 'customers');
?>
<div class="grid lg:grid-cols-3 gap-6">
  <section class="lg:col-span-2 space-y-6">
    <div class="border rounded-xl bg-white p-5">
      <h2 class="text-lg font-semibold text-slate-700 mb-3">Genel Bilgiler</h2>
      <dl class="grid md:grid-cols-2 gap-3 text-sm text-slate-600">
        <div><span class="font-medium text-slate-700">Ad Soyad:</span> <?= h($customer['full_name']) ?></div>
        <div><span class="font-medium text-slate-700">E-posta:</span> <?= h($customer['email']) ?></div>
        <div><span class="font-medium text-slate-700">Telefon:</span> <?= h($customer['phone']) ?></div>
        <div><span class="font-medium text-slate-700">Toplam Randevu:</span> <?= (int)$customer['total_count'] ?></div>
        <div><span class="font-medium text-slate-700">Son Randevu:</span> <?= $latest ? h(date('d.m.Y H:i', strtotime($latest['app_date'].' '.$latest['app_time']))) : '-' ?></div>
        <div><span class="font-medium text-slate-700">Son Personel:</span> <?= h($latest['provider_name'] ?? '-') ?></div>
      </dl>
    </div>
    <div class="border rounded-xl bg-white p-5">
      <h2 class="text-lg font-semibold text-slate-700 mb-3">Randevu Geçmişi</h2>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
            <tr>
              <th class="px-3 py-2 text-left">Tarih</th>
              <th class="px-3 py-2 text-left">Saat</th>
              <th class="px-3 py-2 text-left">Personel</th>
              <th class="px-3 py-2 text-left">Durum</th>
              <th class="px-3 py-2 text-left">Not</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($history as $item): ?>
              <tr class="border-t">
                <td class="px-3 py-2 text-slate-700"><?= h(date('d.m.Y', strtotime($item['app_date']))) ?></td>
                <td class="px-3 py-2 text-slate-600"><?= h($item['app_time']) ?></td>
                <td class="px-3 py-2 text-slate-600"><?= h($item['provider_name'] ?? '') ?></td>
                <td class="px-3 py-2 text-slate-600"><?= h(appointment_statuses()[$item['status']] ?? $item['status']) ?></td>
                <td class="px-3 py-2 text-slate-500"><?= nl2br(h($item['note'])) ?></td>
              </tr>
            <?php endforeach; if (!$history): ?>
              <tr><td colspan="5" class="px-3 py-4 text-center text-slate-400">Geçmiş bulunamadı.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php if ($log): ?>
      <div class="border rounded-xl bg-white p-5">
        <h2 class="text-sm font-semibold text-slate-700 mb-3">Son İşlemler</h2>
        <ul class="space-y-2 text-sm text-slate-600">
          <?php foreach ($log as $entry): ?>
            <li><?= h($entry) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
  </section>
  <section class="space-y-6">
    <div class="border rounded-xl bg-white p-5">
      <h2 class="text-sm font-semibold text-slate-700 mb-3">Hızlı E-posta</h2>
      <form method="post" class="space-y-3">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="quick_email">
        <label class="text-xs text-slate-500">Şablon Konu
          <input type="text" name="subject" value="<?= h($templates['email']['bulk_default']['subject']) ?>" class="mt-1 border rounded w-full p-2 text-sm">
        </label>
        <label class="text-xs text-slate-500">Şablon İçerik
          <textarea name="body" rows="4" class="mt-1 border rounded w-full p-2 text-xs font-mono"><?= h($templates['email']['bulk_default']['body']) ?></textarea>
        </label>
        <label class="text-xs text-slate-500">Ek Mesaj
          <textarea name="message" rows="3" class="mt-1 border rounded w-full p-2 text-sm"></textarea>
        </label>
        <button class="px-4 py-2 rounded-lg bg-slate-900 text-white w-full">E-posta Gönder</button>
      </form>
    </div>
    <div class="border rounded-xl bg-white p-5">
      <h2 class="text-sm font-semibold text-slate-700 mb-3">Hızlı SMS</h2>
      <form method="post" class="space-y-3">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="quick_sms">
        <label class="text-xs text-slate-500">Şablon
          <select name="sms_template" class="mt-1 border rounded w-full p-2 text-sm">
            <?php foreach ($templates['sms'] as $key => $tpl): ?>
              <option value="<?= h($tpl) ?>"><?= h(strtoupper(str_replace('_',' ', $key))) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="text-xs text-slate-500">Ek Mesaj
          <textarea name="message" rows="3" class="mt-1 border rounded w-full p-2 text-sm"></textarea>
        </label>
        <button class="px-4 py-2 rounded-lg bg-slate-900 text-white w-full">SMS Gönder</button>
      </form>
    </div>
  </section>
</div>
<?php
admin_render_footer();
