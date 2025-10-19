<?php
// public/_progress.php — Modern, ikonlu progress
$steps = [
  'book'    => ['label' => t('progress.book'),    'icon' => 'user'],
  'date'    => ['label' => t('progress.date'),    'icon' => 'calendar'],
  'time'    => ['label' => t('progress.time'),    'icon' => 'clock'],
  'confirm' => ['label' => t('progress.confirm'), 'icon' => 'checkdoc'],
];
$order  = array_keys($steps);
$curr   = $_GET['p'] ?? 'book';
$index  = array_search($curr, $order, true);
if ($curr === 'done') $index = count($order) - 1;
if ($index === false) $index = 0;

function ico($name,$cls='w-5 h-5'){
  $p=[
    'user'=>'<path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.33 0-6 2-6 4v2h12v-2c0-2-2.67-4-6-4Z"/>',
    'calendar'=>'<path d="M7 2v2H5a2 2 0 0 0-2 2v2h18V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2Zm14 8H3v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V10Z"/>',
    'clock'=>'<path d="M12 2a10 10 0 1 0 10 10A10.01 10.01 0 0 0 12 2Zm1 10.41 3.3 1.9-.96 1.66L11 13V7h2Z"/>',
    'checkdoc'=>'<path d="M14 2H6a2 2 0 0 0-2 2v16l4-2 4 2 4-2 4 2V8Zm5 15.47-3-1.5-4 2-4-2-3 1.5V4h9v4h5ZM8.5 11.5l-1-1.06L9 8.94l1.5 1.5 3.5-3.5 1.06 1.06-4.56 4.5Z"/>'
  ];
  return '<svg viewBox="0 0 24 24" fill="currentColor" class="'.$cls.'">'.$p[$name].'</svg>';
}

$total = count($order);
$progress = (($index) / max(1, $total-1)) * 100; // 0..100
?>
<div class="px-6 pt-5 pb-4">
  <!-- Çizgi -->
  <div class="relative mb-6">
    <div class="h-1 bg-slate-200 rounded-full"></div>
    <div class="absolute inset-y-0 left-0" style="width: <?= $progress ?>%">
      <div class="h-1 bg-gradient-to-r from-emerald-500 to-sky-500 rounded-full"></div>
    </div>
  </div>

  <!-- Adımlar -->
  <div class="grid grid-cols-4 gap-2">
    <?php foreach($order as $i=>$k):
      $state = $i < $index ? 'done' : ($i===$index ? 'current' : 'todo');
      $dot = [
        'done'    => 'bg-sky-600 text-white ring-4 ring-sky-100',
        'current' => 'bg-emerald-600 text-white ring-4 ring-emerald-100',
        'todo'    => 'bg-white text-slate-400 ring-2 ring-slate-200'
      ][$state];
      $label = [
        'done'    => 'text-slate-700',
        'current' => 'text-emerald-700',
        'todo'    => 'text-slate-500'
      ][$state];
    ?>
    <div class="flex flex-col items-center">
      <div class="w-10 h-10 rounded-full flex items-center justify-center <?= $dot ?>">
        <?= ico($steps[$k]['icon']) ?>
      </div>
      <div class="mt-2 text-[12px] text-center leading-tight <?= $label ?>"><?= h($steps[$k]['label']) ?></div>
      <div class="mt-0.5 text-[10px] <?= $state==='done'?'text-sky-600':'text-transparent' ?>"><?= h(t('progress.completed')) ?></div>
      <?php if($state==='current'): ?>
        <div class="mt-0.5 inline-flex items-center gap-1 text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full text-[10px]"><?= h(t('progress.here')) ?></div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>
