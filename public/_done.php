<?php
$id  = (int)($_SESSION['last_id'] ?? 0);
$app = null;
if ($id) {
  $app = q($pdo, 'SELECT a.*, p.name provider FROM appointments a JOIN providers p ON p.id=a.provider_id WHERE a.id=?', [$id])->fetch();
}
?>
<?php if ($app):
  $ts       = strtotime($app['app_date'].' '.$app['app_time']);
  $day      = date('d', $ts);
  $timeStr  = $app['app_time'];
  $dateStr  = date('d.m.Y', strtotime($app['app_date']));
  $weekTR   = ['Pzt','Sal','Çar','Per','Cum','Cmt','Paz'];
  $weekday  = $weekTR[(int)date('N', $ts)-1] ?? '';
  $moTR     = ['01'=>'Oca','02'=>'Şub','03'=>'Mar','04'=>'Nis','05'=>'May','06'=>'Haz','07'=>'Tem','08'=>'Ağu','09'=>'Eyl','10'=>'Eki','11'=>'Kas','12'=>'Ara'];
  $mo       = $moTR[date('m',$ts)] ?? date('M',$ts);
  $yr       = date('Y',$ts);
  $duration = (int)($app['duration_minutes'] ?? 30);
  $dtStart  = date('Ymd\THis', $ts);
  $dtEnd    = date('Ymd\THis', strtotime('+'.$duration.' minutes', $ts));
  $summary  = 'Randevu - '.$app['provider'];
  $desc     = "Randevu: {$app['provider']}\\nTarih: {$dateStr} {$timeStr}\\nNot: ".trim((string)$app['note']);
  $ics      = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//RandevuPro//v2.0//TR\r\nBEGIN:VEVENT\r\nUID:appt-{$app['id']}@randevupro\r\nDTSTAMP:".gmdate('Ymd\THis\Z')."\r\nDTSTART:{$dtStart}\r\nDTEND:{$dtEnd}\r\nSUMMARY:{$summary}\r\nDESCRIPTION:{$desc}\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n";
  $icsHref  = 'data:text/calendar;charset=utf-8,'.rawurlencode($ics);
?>
<div class="p-6 space-y-6">
  <div class="flex items-center justify-center gap-3">
    <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center ring-8 ring-emerald-50">✔</div>
    <div>
      <h2 class="text-2xl font-semibold text-slate-800"><?= h(t('success.title')) ?></h2>
      <p class="text-sm text-slate-500"><?= h(t('success.text')) ?></p>
    </div>
  </div>
  <div class="grid lg:grid-cols-3 gap-6">
    <div class="theme-surface rounded-2xl border shadow-sm p-5 relative overflow-hidden">
      <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-rose-50"></div>
      <div class="relative">
        <div class="text-sm text-slate-500 mb-3"><?= h($mo.' '.$yr) ?> • <?= h($weekday) ?></div>
        <div class="flex items-center gap-4">
          <div class="w-24 h-24 rounded-full bg-rose-600 text-white flex flex-col items-center justify-center shadow-lg">
            <div class="text-lg leading-none"><?= h($mo) ?></div>
            <div class="text-3xl font-bold leading-tight"><?= h($day) ?></div>
          </div>
          <div class="flex-1">
            <div class="text-slate-500 text-sm">Randevu</div>
            <div class="text-xl font-semibold text-slate-800"><?= h($dateStr.' • '.$timeStr) ?></div>
            <div class="mt-1 text-slate-500 text-sm"><?= h($duration) ?> dk</div>
          </div>
        </div>
      </div>
    </div>
    <div class="theme-surface rounded-2xl border shadow-sm p-5 lg:col-span-2">
      <div class="grid md:grid-cols-3 gap-4">
        <div class="p-4 rounded-xl bg-slate-50 border">
          <div class="text-xs text-slate-500">ID</div>
          <div class="text-lg font-medium">#<?= (int)$app['id'] ?></div>
        </div>
        <div class="p-4 rounded-xl bg-slate-50 border">
          <div class="text-xs text-slate-500"><?= h(t('confirm.provider')) ?></div>
          <div class="text-lg font-medium"><?= h($app['provider']) ?></div>
        </div>
        <div class="p-4 rounded-xl bg-slate-50 border">
          <div class="text-xs text-slate-500"><?= h(t('confirm.date')) ?> • <?= h(t('confirm.time')) ?></div>
          <div class="text-lg font-medium"><?= h($dateStr.' • '.$timeStr) ?></div>
        </div>
        <div class="p-4 rounded-xl bg-slate-50 border md:col-span-3">
          <div class="text-xs text-slate-500 mb-1"><?= h(t('book.full_name')) ?> • <?= h(t('book.phone')) ?></div>
          <div class="text-slate-700">
            <?= h($app['full_name']) ?>
            <?php if(!empty($app['phone'])): ?>
              <span class="text-slate-400"> • </span><a class="underline" href="tel:<?= h($app['phone']) ?>"><?= h($app['phone']) ?></a>
            <?php endif; ?>
            <?php if(!empty($app['email'])): ?>
              <span class="text-slate-400"> • </span><a class="underline" href="mailto:<?= h($app['email']) ?>"><?= h($app['email']) ?></a>
            <?php endif; ?>
          </div>
          <?php if(!empty($app['note'])): ?>
            <div class="text-xs text-slate-500 mt-2"><?= h(t('book.note')) ?>: <span class="text-slate-700"><?= nl2br(h($app['note'])) ?></span></div>
          <?php endif; ?>
        </div>
      </div>
      <div class="mt-5 flex flex-wrap gap-3">
        <a href="<?= $icsHref ?>" download="randevu-<?= (int)$app['id'] ?>.ics" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border hover:border-slate-400">
          <?= h(t('done.download_ics')) ?>
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border hover:border-slate-400">
          <?= h(t('done.print')) ?>
        </button>
        <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl btn-primary" href="/">
          <?= h(t('done.new_appointment')) ?>
        </a>
      </div>
    </div>
  </div>
</div>
<?php else: ?>
  <div class="p-8 text-center">
    <div class="text-slate-500 mb-4"><?= h(t('done.no_appointment')) ?></div>
    <a class="px-4 py-2 rounded-xl btn-primary" href="/"><?= h(t('done.new_appointment')) ?></a>
  </div>
<?php endif; ?>
