
<?php
ob_start(); ?>
<div class="tw-flex tw-items-center tw-gap-3 tw-mb-4">
  <form method="get" class="tw-flex tw-gap-2 tw-items-end">
    <div>
      <label class="tw-block tw-text-xs tw-text-gray-500">Difficulty</label>
      <select name="difficulty" class="form-select">
        <option value="">All</option>
        <?php foreach (['easy','medium','hard','difficult'] as $d): ?>
          <option value="<?= $d ?>" <?= (isset($filters['difficulty']) && $filters['difficulty']===$d)?'selected':'' ?>><?= ucfirst($d) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="tw-block tw-text-xs tw-text-gray-500">Length</label>
      <select name="length" class="form-select">
        <option value="">All</option>
        <?php foreach (['short','long','simple'] as $d): ?>
          <option value="<?= $d ?>" <?= (isset($filters['length']) && $filters['length']===$d)?'selected':'' ?>><?= ucfirst($d) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="tw-self-end">
      <button class="btn btn-primary">Filtrele</button>
    </div>
  </form>
</div>

<div class="tw-grid md:tw-grid-cols-2 tw-gap-4">
  <?php foreach (($items??[]) as $r): ?>
    <article class="tw-bg-white tw-border tw-rounded-2xl tw-p-4">
      <h2 class="tw-text-lg tw-font-semibold tw-mb-2">
        <a href="/riddle/<?= h($r['slug']) ?>" class="tw-text-gray-900 hover:tw-text-indigo-600"><?= h($r['title']) ?></a>
      </h2>
      <p class="tw-text-gray-700 tw-line-clamp-3"><?= nl2br(h(mb_substr($r['body'],0,160))) ?>...</p>
      <div class="tw-mt-3 tw-flex tw-gap-2 tw-text-xs tw-text-gray-500">
        <span class="tw-px-2 tw-py-0.5 tw-rounded tw-bg-gray-100"><?= h($r['difficulty']) ?></span>
        <span class="tw-px-2 tw-py-0.5 tw-rounded tw-bg-gray-100"><?= h($r['length']) ?></span>
      </div>
    </article>
  <?php endforeach; ?>
</div>

<?php paginator($page,$per,$total); ?>
<?php $content = ob_get_clean(); include __DIR__.'/layouts/base.php'; ?>
