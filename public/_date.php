<?php
if (empty($_SESSION['book']['pid'])) redirect('/');
$booking = booking_settings($pdo);
$pid     = (int)$_SESSION['book']['pid'];

$today      = new DateTime('today');
$startDate  = (clone $today)->modify('+' . (int)$booking['lead_days'] . ' days');
$endDate    = (clone $today)->modify('+' . (int)$booking['max_days'] . ' days');

$monthParam = $_GET['m'] ?? $startDate->format('Y-m');
try {
  $first = new DateTime($monthParam.'-01');
} catch (Throwable $e) {
  $first = new DateTime($startDate->format('Y-m').'-01');
}

if ($first < (clone $startDate)->modify('first day of this month')) {
  $first = new DateTime($startDate->format('Y-m').'-01');
}
if ($first > (clone $endDate)->modify('first day of next month')) {
  $first = new DateTime($endDate->format('Y-m').'-01');
}

$prev = (clone $first)->modify('-1 month');
$next = (clone $first)->modify('+1 month');
$prevAllowed = $prev >= (clone $startDate)->modify('first day of this month');
$nextAllowed = $next <= (clone $endDate)->modify('first day of this month');

if (isset($_GET['d'])) {
  $d = $_GET['d'];
  if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
    $selected = new DateTime($d);
    if ($selected >= $startDate && $selected <= $endDate) {
      $slots = provider_slots_for_date($pdo, $pid, $d);
      if (!empty($slots)) {
        $_SESSION['book']['date'] = $d;
        redirect('/?p=time');
      }
    }
  }
  redirect('/?p=date');
}

$daysInMonth = (int)$first->format('t');
$monthName   = format_month_year($first);
?>
<div class="p-6">
  <div class="flex items-center justify-between mb-4">
    <div class="text-slate-600 font-medium"><?= h(t('book.date')) ?></div>
    <div class="flex items-center gap-2 text-sm">
      <a class="px-3 py-2 rounded-xl border <?= $prevAllowed ? 'hover:border-slate-400' : 'opacity-50 pointer-events-none' ?>" href="<?= $prevAllowed ? '/?p=date&m='.$prev->format('Y-m') : '#' ?>">←</a>
      <div class="font-medium text-slate-700"><?= h($monthName) ?></div>
      <a class="px-3 py-2 rounded-xl border <?= $nextAllowed ? 'hover:border-slate-400' : 'opacity-50 pointer-events-none' ?>" href="<?= $nextAllowed ? '/?p=date&m='.$next->format('Y-m') : '#' ?>">→</a>
    </div>
  </div>
  <div class="grid grid-cols-7 gap-2 text-center text-xs text-slate-600 mb-2">
    <div>Pzt</div><div>Sal</div><div>Çar</div><div>Per</div><div>Cum</div><div>Cmt</div><div>Paz</div>
  </div>
  <div class="grid grid-cols-7 gap-2 text-sm">
    <?php
    $startDow = (int)$first->format('N');
    for ($i = 1; $i < $startDow; $i++): ?>
      <div></div>
    <?php endfor; ?>
    <?php
    for ($d = 1; $d <= $daysInMonth; $d++):
      $dateStr = $first->format('Y-m').'-'.str_pad((string)$d, 2, '0', STR_PAD_LEFT);
      $current = new DateTime($dateStr);
      if ($current < $startDate || $current > $endDate) {
        echo '<div class="px-3 py-3 rounded-xl border text-center text-slate-300 bg-slate-50">'.$d.'</div>';
        continue;
      }
      $weekday = (int)$current->format('N');
      if (!$booking['allow_weekend'] && ($weekday === 6 || $weekday === 7)) {
        echo '<div class="px-3 py-3 rounded-xl border text-center text-slate-300 bg-slate-50">'.$d.'</div>';
        continue;
      }
      $slots = provider_slots_for_date($pdo, $pid, $dateStr);
      $available = !empty($slots);
      $cls = 'px-3 py-3 rounded-xl border text-center flex items-center justify-center gap-1 date-card ';
      if ($available) {
        $cls .= 'available bg-white';
      } else {
        $cls .= 'disabled opacity-40 pointer-events-none bg-slate-50 text-slate-400';
      }
      $label = date('d.m.Y', strtotime($dateStr));
      $badge = $available ? '✅' : '⛔';
    ?>
      <a class="<?= $cls ?>" href="<?= $available ? '/?p=date&d='.$dateStr : '#' ?>"><?= $badge ?> <?= h($label) ?></a>
    <?php endfor; ?>
  </div>
  <div class="flex justify-between mt-6">
    <a href="/?p=book&pid=<?= $pid ?>" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-600 hover:border-slate-400 transition">
      <?= h(t('btn.prev')) ?>
    </a>
  </div>
</div>
