<div class="p-6">
  <h2 class="text-xl font-semibold mb-6"><?= h(t('home.choose_provider')) ?></h2>
  <div class="grid md:grid-cols-3 gap-5">
  <?php
    $rows = q($pdo, "SELECT id,name,image,COALESCE(bio,'') as bio,primary_color,secondary_color FROM providers WHERE active=1 ORDER BY sort,name")->fetchAll();
    foreach($rows as $r):
      $img = ($r['image'] && file_exists(__DIR__.'/uploads/providers/'.$r['image'])) ? '/uploads/providers/'.$r['image'] : 'https://placehold.co/240x160?text=+';
      $gradient = ($r['primary_color'] && $r['secondary_color']) ? 'background: linear-gradient(135deg, '.h($r['primary_color']).', '.h($r['secondary_color']).');' : '';
  ?>
    <div class="rounded-2xl border shadow-sm overflow-hidden group theme-surface">
      <div class="h-40 w-full overflow-hidden relative">
        <img src="<?= $img ?>" alt="<?= h($r['name']) ?>" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500">
        <?php if($gradient): ?><div class="absolute inset-x-0 bottom-0 h-12 opacity-70" style="<?= $gradient ?>"></div><?php endif; ?>
      </div>
      <div class="p-4 space-y-3">
        <div class="font-medium text-lg text-slate-800 flex items-center justify-between">
          <span><?= h($r['name']) ?></span>
          <?php if($gradient): ?><span class="w-3 h-3 rounded-full border" style="background: <?= h($r['primary_color']) ?>"></span><?php endif; ?>
        </div>
        <?php if(!empty($r['bio'])): ?>
          <div class="text-sm text-slate-500 leading-relaxed max-h-28 overflow-hidden"><?= nl2br(h($r['bio'])) ?></div>
        <?php endif; ?>
        <div>
          <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl btn-primary" href="/?p=book&pid=<?= $r['id'] ?>">
            <?= h(t('home.book_button')) ?>
          </a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (empty($rows)): ?>
    <div class="p-6 text-slate-500 bg-white/60 rounded-xl border border-dashed col-span-full text-center">
      <?= h(t('home.no_provider')) ?>
    </div>
  <?php endif; ?>
  </div>
</div>
