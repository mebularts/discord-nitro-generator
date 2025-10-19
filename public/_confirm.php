<?php
if (empty($_SESSION['book']['time'])) redirect('/?p=time');
$b = $_SESSION['book'];
$providerRow = q($pdo, 'SELECT name,email,phone FROM providers WHERE id=?', [$b['pid']])->fetch();
$providerName = $providerRow['name'] ?? '';
$duration = provider_default_duration($pdo, (int)$b['pid']);
$langCode = current_language_code($pdo);

if (is_post()) {
  csrf_check();
  $clientIp = $_SESSION['book']['client_ip'] ?? request_ip();
  $fingerprint = $_SESSION['book']['fingerprint'] ?? customer_fingerprint(
    $b['full_name'] ?? '',
    $b['birth'] ?? '',
    normalize_phone($b['phone'] ?? ''),
    $b['email'] ?? ''
  );
  $duplicate = duplicate_appointment_exists($pdo, $clientIp, $fingerprint);
  if ($duplicate) {
    $_SESSION['book']['duplicate_lock'] = $duplicate;
    redirect('/?p=date');
  }
  $appId = null;
  q($pdo, 'INSERT INTO appointments (provider_id, full_name, gender, birth, phone, email, note, app_date, app_time, duration_minutes, status, lang_code, client_ip, customer_fingerprint, created_at, reminder_sent) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?, NOW(),0)', [
    $b['pid'],
    $b['full_name'],
    $b['gender'],
    $b['birth'],
    $b['phone'],
    $b['email'],
    $b['note'],
    $b['date'],
    $b['time'],
    $duration,
    'new',
    $langCode,
    $clientIp,
    $fingerprint,
  ]);
  $appId = (int)$pdo->lastInsertId();
  $_SESSION['last_id'] = $appId;
  $_SESSION['book'] = [];
  if (!empty($b['email'])) {
    $context = [
      'appointment_id'   => $appId,
      'customer_name'    => $b['full_name'],
      'provider_name'    => $providerName,
      'appointment_date' => date('d.m.Y', strtotime($b['date'])),
      'appointment_time' => $b['time'],
      'message'          => '',
    ];
    $tpl = notification_template($pdo, 'email', 'appointment_customer');
    $subject = format_notification($tpl['subject'] ?? 'Randevu Onayı', $context);
    $body    = format_notification($tpl['body'] ?? '', $context);
    send_email($pdo, $b['email'], $subject, $body);
  }
  if (!empty($b['phone'])) {
    $context = [
      'appointment_id'   => $appId,
      'customer_name'    => $b['full_name'],
      'provider_name'    => $providerName,
      'appointment_date' => date('d.m.Y', strtotime($b['date'])),
      'appointment_time' => $b['time'],
      'message'          => '',
    ];
    $msg = notification_template($pdo, 'sms', 'appointment_customer');
    send_sms($pdo, $b['phone'], format_notification($msg, $context));
  }
  if (!empty($providerRow['email'])) {
    $context = [
      'appointment_id'   => $appId,
      'customer_name'    => $b['full_name'],
      'provider_name'    => $providerName,
      'appointment_date' => date('d.m.Y', strtotime($b['date'])),
      'appointment_time' => $b['time'],
      'message'          => '',
    ];
    $tpl = notification_template($pdo, 'email', 'appointment_provider');
    $subject = format_notification($tpl['subject'] ?? 'Yeni Randevu', $context);
    $body    = format_notification($tpl['body'] ?? '', $context);
    send_email($pdo, $providerRow['email'], $subject, $body);
  }
  if (!empty($providerRow['phone'])) {
    $context = [
      'appointment_id'   => $appId,
      'customer_name'    => $b['full_name'],
      'provider_name'    => $providerName,
      'appointment_date' => date('d.m.Y', strtotime($b['date'])),
      'appointment_time' => $b['time'],
      'message'          => '',
    ];
    $tpl = notification_template($pdo, 'sms', 'appointment_provider');
    send_sms($pdo, $providerRow['phone'], format_notification($tpl, $context));
  }
  redirect('/?p=done');
}
?>
<div class="p-6 space-y-6">
  <div class="text-center">
    <h2 class="text-xl font-semibold text-slate-800 mb-2"><?= h(t('confirm.title')) ?></h2>
    <p class="text-sm text-slate-500"><?= h(t('confirm.info')) ?></p>
  </div>
  <div class="grid md:grid-cols-3 gap-4 text-center">
    <div class="bg-slate-50 rounded-xl p-6 border">
      <div class="text-sm text-slate-500"><?= h(t('confirm.provider')) ?></div>
      <div class="text-xl font-semibold text-slate-800"><?= h($providerName) ?></div>
    </div>
    <div class="bg-slate-50 rounded-xl p-6 border">
      <div class="text-sm text-slate-500"><?= h(t('confirm.date')) ?></div>
      <div class="text-xl font-semibold text-slate-800"><?= date('d.m.Y', strtotime($b['date'])) ?></div>
    </div>
    <div class="bg-slate-50 rounded-xl p-6 border">
      <div class="text-sm text-slate-500"><?= h(t('confirm.time')) ?></div>
      <div class="text-xl font-semibold text-slate-800"><?= h($b['time']) ?> (<?= h($duration) ?> dk)</div>
    </div>
  </div>
  <div class="bg-white rounded-xl border p-4">
    <h3 class="text-sm font-semibold text-slate-600 mb-3"><?= h(t('confirm.info')) ?></h3>
    <dl class="grid md:grid-cols-2 gap-3 text-sm text-slate-600">
      <div><span class="font-medium text-slate-700"><?= h(t('book.full_name')) ?>:</span> <?= h($b['full_name']) ?></div>
      <div><span class="font-medium text-slate-700"><?= h(t('book.phone')) ?>:</span> <?= h($b['phone']) ?></div>
      <div><span class="font-medium text-slate-700"><?= h(t('book.gender')) ?>:</span> <?= h($b['gender']) ?></div>
      <div><span class="font-medium text-slate-700"><?= h(t('book.email')) ?>:</span> <?= h($b['email']) ?></div>
      <div><span class="font-medium text-slate-700"><?= h(t('book.birth')) ?>:</span> <?= h($b['birth']) ?></div>
      <?php if (!empty($b['note'])): ?>
        <div class="md:col-span-2"><span class="font-medium text-slate-700"><?= h(t('book.note')) ?>:</span> <?= nl2br(h($b['note'])) ?></div>
      <?php endif; ?>
    </dl>
  </div>
  <form method="post" class="flex justify-between">
    <?= csrf_field() ?>
    <a href="/?p=time" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-600 hover:border-slate-400 transition">
      <?= h(t('btn.prev')) ?>
    </a>
    <button class="px-4 py-2 rounded-lg btn-primary">
      <?= h(t('confirm.submit')) ?>
    </button>
  </form>
</div>
