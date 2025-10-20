
<?php ob_start(); ?>
<article class="tw-bg-white tw-border tw-rounded-2xl tw-p-6">
  <h1 class="tw-text-2xl tw-font-semibold tw-mb-3"><?= h($p['title']) ?></h1>
  <div class="tw-prose"><?= $p['body'] ?></div>
</article>
<?php $content = ob_get_clean(); include __DIR__.'/../layouts/base.php'; ?>
