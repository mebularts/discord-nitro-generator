<?php
require_once __DIR__.'/bootstrap.php';
must_login(['notifications']);

if (!function_exists('collect_recipient_candidates')) {
  function collect_recipient_candidates(PDO $pdo, array $filters): array {
    $provider = (int)($filters['provider'] ?? 0);
    $status   = trim((string)($filters['status'] ?? ''));
    $start    = trim((string)($filters['start'] ?? ''));
    $end      = trim((string)($filters['end'] ?? ''));

    $where = [];
    $params = [];
    if ($provider > 0) {
      $where[] = 'a.provider_id = ?';
      $params[] = $provider;
    }
    if ($status !== '') {
      $where[] = 'a.status = ?';
      $params[] = $status;
    }
    if ($start !== '') {
      $where[] = 'a.app_date >= ?';
      $params[] = $start;
    }
    if ($end !== '') {
      $where[] = 'a.app_date <= ?';
      $params[] = $end;
    }

    $sql = 'SELECT a.*, p.name AS provider_name, COALESCE(NULLIF(a.email, ""), a.phone) AS customer_key
            FROM appointments a
            JOIN providers p ON p.id = a.provider_id';
    if ($where) {
      $sql .= ' WHERE '.implode(' AND ', $where);
    }
    $sql .= ' ORDER BY a.app_date DESC, a.id DESC LIMIT 500';

    $rows = q($pdo, $sql, $params)->fetchAll();
    $result = [];
    foreach ($rows as $row) {
      $key = $row['customer_key'];
      if (!$key) continue;
      if (!isset($result[$key])) {
        $result[$key] = $row + ['customer_key' => $key];
      }
    }
    return array_values($result);
  }
}

$providers = q($pdo, 'SELECT id, name FROM providers ORDER BY name')->fetchAll();
$statusOptions = appointment_statuses();
$templates = notification_templates($pdo);

$filters = [
  'provider' => (int)($_GET['provider'] ?? 0),
  'status'   => $_GET['status'] ?? '',
  'start'    => $_GET['start'] ?? '',
  'end'      => $_GET['end'] ?? '',
];
$candidates = collect_recipient_candidates($pdo, $filters);

$log = [];

if (is_post()) {
  csrf_check();
  $action = $_POST['action'] ?? '';
  if ($action === 'send') {
    $channel      = $_POST['channel'] === 'sms' ? 'sms' : 'email';
    $templateKey  = $_POST['template'] ?? 'bulk_default';
    $messageInput = trim((string)($_POST['message'] ?? ''));
    $manualRaw    = trim((string)($_POST['recipients'] ?? ''));
    $manualList   = [];
    if ($manualRaw !== '') {
      $parts = preg_split('/[\s,;\n\r]+/', $manualRaw);
      foreach ($parts as $part) {
        $part = trim($part);
        if ($part === '') continue;
        $manualList[] = $part;
      }
    }

    $sendFilters = [
      'provider' => (int)($_POST['provider'] ?? 0),
      'status'   => $_POST['status'] ?? '',
      'start'    => $_POST['start'] ?? '',
      'end'      => $_POST['end'] ?? '',
    ];
    $targetRows = !empty($_POST['use_filters']) ? collect_recipient_candidates($pdo, $sendFilters) : [];

    $targets = [];

    foreach ($targetRows as $row) {
      $key = $row['customer_key'];
      if (!$key) continue;
      $targets[$key] = [
        'email'     => $row['email'] ?? '',
        'phone'     => $row['phone'] ?? '',
        'full_name' => $row['full_name'] ?? '',
        'provider'  => $row['provider_name'] ?? '',
        'appointment' => $row,
      ];
    }

    foreach ($manualList as $manual) {
      if ($channel === 'email') {
        if (!filter_var($manual, FILTER_VALIDATE_EMAIL)) continue;
        $targets[$manual] = [
          'email' => $manual,
          'phone' => '',
          'full_name' => '',
          'provider' => '',
          'appointment' => null,
        ];
      } else {
        $phone = preg_replace('/[^0-9+]/', '', $manual);
        if ($phone === '') continue;
        $targets[$phone] = [
          'email' => '',
          'phone' => $phone,
          'full_name' => '',
          'provider' => '',
          'appointment' => null,
        ];
      }
    }

    $targets = array_values($targets);

    if ($targets) {
      if ($channel === 'email') {
        $tpl = notification_template($pdo, 'email', $templateKey);
        $subjectTpl = $tpl['subject'] ?? 'Bilgilendirme';
        $bodyTpl    = $tpl['body'] ?? '{message}';
        foreach ($targets as $target) {
          $key = $target['email'] ?: ($target['appointment']['customer_key'] ?? '');
          if ($key) {
            $appointment = $target['appointment'] ?? ($key ? latest_customer_appointment($pdo, $key) : null);
          } else {
            $appointment = null;
          }
          $context = [
            'appointment_id'   => $appointment['id'] ?? '-',
            'customer_name'    => $target['full_name'] ?: ($appointment['full_name'] ?? ''),
            'provider_name'    => $target['provider'] ?: ($appointment['provider_name'] ?? ''),
            'appointment_date' => isset($appointment['app_date']) ? date('d.m.Y', strtotime($appointment['app_date'])) : '',
            'appointment_time' => $appointment['app_time'] ?? '',
            'message'          => $messageInput,
          ];
          $subject = format_notification($subjectTpl, $context);
          $body    = format_notification($bodyTpl, $context);
          $ok = send_email($pdo, $target['email'], $subject, $body);
          $log[] = ($ok ? '✅' : '⚠️').' '.($target['email'] ?: 'n/a').' · '.$subject;
        }
      } else {
        $tpl = notification_template($pdo, 'sms', $templateKey);
        foreach ($targets as $target) {
          $phone = $target['phone'] ?: ($target['appointment']['phone'] ?? '');
          if ($phone === '') continue;
          $key = $target['email'] ?: ($target['appointment']['customer_key'] ?? $phone);
          $appointment = $target['appointment'] ?? ($key ? latest_customer_appointment($pdo, $key) : null);
          $context = [
            'appointment_id'   => $appointment['id'] ?? '-',
            'customer_name'    => $target['full_name'] ?: ($appointment['full_name'] ?? ''),
            'provider_name'    => $target['provider'] ?: ($appointment['provider_name'] ?? ''),
            'appointment_date' => isset($appointment['app_date']) ? date('d.m.Y', strtotime($appointment['app_date'])) : '',
            'appointment_time' => $appointment['app_time'] ?? '',
            'message'          => $messageInput,
          ];
          $content = format_notification($tpl, $context);
          $resp = send_sms($pdo, $phone, $content);
          $log[] = ($resp['ok'] ? '✅' : '⚠️').' '.$phone;
        }
      }
      set_flash('success', 'Gönderim işlemi tamamlandı.');
    } else {
      set_flash('error', 'Gönderilecek kişi bulunamadı.');
    }
    $_SESSION['notifications_log'] = $log;
    redirect('/admin/notifications.php?'.http_build_query($sendFilters));
  }
}

if (isset($_SESSION['notifications_log'])) {
  $log = $_SESSION['notifications_log'];
  unset($_SESSION['notifications_log']);
}

admin_render_header('Bildirimler', 'notifications');
?>
<div class="grid lg:grid-cols-3 gap-6">
  <section class="lg:col-span-2 space-y-6">
    <form method="post" class="border rounded-xl bg-white p-5 space-y-5">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="send">
      <div class="flex flex-wrap gap-4 items-center">
        <label class="text-sm text-slate-600 flex items-center gap-2">
          <input type="radio" name="channel" value="email" checked> E-posta
        </label>
        <label class="text-sm text-slate-600 flex items-center gap-2">
          <input type="radio" name="channel" value="sms"> SMS
        </label>
        <label class="text-sm text-slate-600">
          Şablon
          <select name="template" class="mt-1 border rounded-lg p-2 text-sm">
            <?php $templateKeys = array_unique(array_merge(array_keys($templates['email']), array_keys($templates['sms']))); ?>
            <?php foreach ($templateKeys as $key): ?>
              <option value="<?= h($key) ?>" <?= $key === 'bulk_default' ? 'selected' : '' ?>><?= h(strtoupper(str_replace('_',' ', $key))) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
      </div>
      <div>
        <label class="text-sm text-slate-600">Ek Mesaj (Şablondaki {message} yerine geçer)</label>
        <textarea name="message" rows="4" class="mt-1 border rounded-lg w-full p-2" placeholder="Duyurunuz..."></textarea>
      </div>
      <div>
        <label class="text-sm text-slate-600">Manuel Alıcılar (virgül veya satır ile ayırın)</label>
        <textarea name="recipients" rows="3" class="mt-1 border rounded-lg w-full p-2 font-mono text-xs" placeholder="mail@example.com, 05550000000"></textarea>
      </div>
      <div class="grid md:grid-cols-4 gap-4">
        <input type="hidden" name="provider" value="<?= (int)$filters['provider'] ?>">
        <input type="hidden" name="status" value="<?= h($filters['status']) ?>">
        <input type="hidden" name="start" value="<?= h($filters['start']) ?>">
        <input type="hidden" name="end" value="<?= h($filters['end']) ?>">
        <label class="md:col-span-4 text-sm text-slate-600 flex items-center gap-2">
          <input type="checkbox" name="use_filters" value="1" <?= $filters['provider'] || $filters['status'] || $filters['start'] || $filters['end'] ? 'checked' : '' ?>>
          Filtrelenen müşterilere gönder
        </label>
      </div>
      <div>
        <button class="px-4 py-2 rounded-lg bg-slate-900 text-white">Gönderimi Başlat</button>
      </div>
    </form>
    <?php if ($log): ?>
      <div class="border rounded-xl bg-white p-5">
        <h2 class="text-sm font-semibold text-slate-700 mb-3">Son Gönderim</h2>
        <ul class="space-y-2 text-sm text-slate-600">
          <?php foreach ($log as $entry): ?>
            <li><?= h($entry) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
  </section>
  <section class="border rounded-xl bg-white p-5 space-y-4">
    <h2 class="text-lg font-semibold text-slate-700">Hedef Filtreleri</h2>
    <form method="get" class="space-y-4">
      <div>
        <label class="text-xs text-slate-500">Personel</label>
        <select name="provider" class="mt-1 border rounded-lg w-full p-2">
          <option value="0">Tümü</option>
          <?php foreach ($providers as $prov): ?>
            <option value="<?= (int)$prov['id'] ?>" <?= $filters['provider'] === (int)$prov['id'] ? 'selected' : '' ?>><?= h($prov['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="text-xs text-slate-500">Durum</label>
        <select name="status" class="mt-1 border rounded-lg w-full p-2">
          <option value="">Tümü</option>
          <?php foreach ($statusOptions as $key => $label): ?>
            <option value="<?= h($key) ?>" <?= $filters['status'] === $key ? 'selected' : '' ?>><?= h($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="text-xs text-slate-500">Başlangıç Tarihi</label>
        <input type="date" name="start" value="<?= h($filters['start']) ?>" class="mt-1 border rounded-lg w-full p-2">
      </div>
      <div>
        <label class="text-xs text-slate-500">Bitiş Tarihi</label>
        <input type="date" name="end" value="<?= h($filters['end']) ?>" class="mt-1 border rounded-lg w-full p-2">
      </div>
      <div>
        <button class="px-4 py-2 rounded-lg bg-slate-900 text-white w-full">Filtrele</button>
      </div>
    </form>
    <div class="border-t pt-4">
      <h3 class="text-sm font-semibold text-slate-700 mb-2">Önizleme (<?= count($candidates) ?> kişi)</h3>
      <div class="max-h-64 overflow-auto text-sm">
        <table class="w-full text-left text-xs">
          <thead class="text-slate-500 uppercase">
            <tr>
              <th class="py-2">Müşteri</th>
              <th class="py-2">E-posta</th>
              <th class="py-2">Telefon</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($candidates as $row): ?>
              <tr class="border-t">
                <td class="py-1 text-slate-600"><?= h($row['full_name']) ?></td>
                <td class="py-1 text-slate-600"><?= h($row['email']) ?></td>
                <td class="py-1 text-slate-600"><?= h($row['phone']) ?></td>
              </tr>
            <?php endforeach; if (!$candidates): ?>
              <tr><td colspan="3" class="py-4 text-center text-slate-400">Kayıt yok</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>
<?php
admin_render_footer();
