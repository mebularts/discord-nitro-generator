<?php
declare(strict_types=1);
/** @var array $page */
?>
<?php ob_start(); ?>
<article class="tw-rounded-3xl tw-bg-slate-900 tw-border tw-border-slate-800 tw-shadow-xl tw-p-6 tw-space-y-4">
    <h1 class="tw-text-3xl tw-font-semibold tw-text-white"><?= htmlspecialchars($page['title'], ENT_QUOTES, 'UTF-8') ?></h1>
    <div class="tw-text-slate-200 tw-leading-relaxed">
        <?= $page['body'] ?>
    </div>
</article>
<?php $content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
