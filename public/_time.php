<?php
if (empty($_SESSION['book']['date'])) redirect('/?p=date');
$date    = $_SESSION['book']['date'];
$pid     = (int)$_SESSION['book']['pid'];
$slots   = provider_slots_for_date($pdo, $pid, $date);
$duration = provider_default_duration($pdo, $pid);

if (isset($_GET['t'])) {
  $t = $_GET['t'];
  if (in_array($t, $slots, true)) {
    $_SESSION['book']['time'] = $t;
    redirect('/?p=confirm');
  }
  redirect('/?p=time');
}

$am = array_filter($slots, fn($s) => strtotime($s) < strtotime('12:00'));
$pm = array_filter($slots, fn($s) => strtotime($s) >= strtotime('12:00'));
?>
<div class="p-6">
  <div class="flex items-center justify-between mb-4">
    <div class="text-slate-600 font-medium"><?= h(t('book.time')) ?></div>
    <div class="text-sm text-slate-500"><?= date('d.m.Y', strtotime($date)) ?></div>
  </div>
  <?php if (empty($slots)): ?>
    <div class="p-6 text-center border border-dashed rounded-xl text-slate-500 bg-white/60">
      <?= h(t('date.unavailable')) ?>
    </div>
  <?php else: ?>
    <div class="mb-2 font-medium text-slate-600"><?= h(t('time.morning')) ?></div>
    <div class="grid md:grid-cols-4 gap-3 mb-6">
      <?php foreach($am as $s): ?>
        <a class="px-4 py-3 text-center border rounded-xl date-card available" href="/?p=time&t=<?= h($s) ?>">
          <?= h($s) ?>
        </a>
      <?php endforeach; if(empty($am)): ?>
        <div class="text-slate-400 text-sm">—</div>
      <?php endif; ?>
    </div>
    <div class="mb-2 font-medium text-slate-600"><?= h(t('time.afternoon')) ?></div>
    <div class="grid md:grid-cols-4 gap-3">
      <?php foreach($pm as $s): ?>
        <a class="px-4 py-3 text-center border rounded-xl date-card available" href="/?p=time&t=<?= h($s) ?>">
          <?= h($s) ?>
        </a>
      <?php endforeach; if(empty($pm)): ?>
        <div class="text-slate-400 text-sm">—</div>
      <?php endif; ?>
    </div>
  <?php endif; ?>
  <div class="flex justify-between mt-6">
    <a href="/?p=date" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-600 hover:border-slate-400 transition">
      <?= h(t('btn.prev')) ?>
    </a>
    <div class="text-sm text-slate-400">
      <?= h($duration) ?> dk
    </div>
  </div>
</div>
