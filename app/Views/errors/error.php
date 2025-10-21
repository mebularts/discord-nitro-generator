<?php
declare(strict_types=1);
?>
<?php ob_start(); ?>
<div class="tw-text-center tw-py-20 tw-space-y-4">
    <div class="tw-text-8xl">⚠️</div>
    <h1 class="tw-text-4xl tw-font-bold tw-text-white"><?= htmlspecialchars($code ?? 500, ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="tw-text-lg tw-text-slate-300"><?= htmlspecialchars($message ?? __('errors.generic'), ENT_QUOTES, 'UTF-8') ?></p>
    <a class="btn btn-light tw-rounded-full tw-px-5" href="/"><?= htmlspecialchars(__('errors.back_home'), ENT_QUOTES, 'UTF-8') ?></a>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
