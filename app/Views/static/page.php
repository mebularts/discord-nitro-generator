<?php
ob_start();
?>
<article class="tw-bg-white tw-border tw-rounded-3xl tw-p-8 tw-shadow-sm">
  <header class="tw-mb-6">
    <h1 class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= h($p['title']) ?></h1>
  </header>
  <div class="tw-prose tw-max-w-none tw-text-gray-800">
    <?= $p['body'] ?>
  </div>
</article>
<?php
$content = ob_get_clean();
$title = $p['title'];
include __DIR__ . '/../layouts/base.php';
