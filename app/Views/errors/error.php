<?php
$title = $title ?? __('error.generic.title', 'Unexpected error');
ob_start();
?>
<section class="tw-min-h-[50vh] tw-flex tw-flex-col tw-items-center tw-justify-center tw-text-center tw-gap-6">
  <div class="tw-max-w-xl tw-space-y-4">
    <span class="tw-inline-flex tw-items-center tw-justify-center tw-w-16 tw-h-16 tw-rounded-3xl tw-bg-amber-100 tw-text-amber-600 tw-text-2xl tw-font-semibold"><?= h($status ?? 500) ?></span>
    <h1 class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= h($title) ?></h1>
    <p class="tw-text-base tw-text-gray-600">
      <?= h($message ?? __('error.generic.body', 'Something went wrong. Please try again later.')) ?>
    </p>
    <a href="/" class="btn btn-outline-primary tw-inline-flex tw-items-center tw-gap-2">
      <span>⬅</span>
      <span><?= __('error.generic.cta', 'Return home') ?></span>
    </a>
  </div>
</section>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
