<?php
$title = $title ?? __('error.404.title', 'Page not found');
ob_start();
?>
<section class="tw-min-h-[50vh] tw-flex tw-flex-col tw-items-center tw-justify-center tw-text-center tw-gap-6">
  <div class="tw-max-w-xl tw-space-y-4">
    <span class="tw-inline-flex tw-items-center tw-justify-center tw-w-16 tw-h-16 tw-rounded-3xl tw-bg-indigo-100 tw-text-indigo-600 tw-text-2xl tw-font-semibold">404</span>
    <h1 class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= h($title) ?></h1>
    <p class="tw-text-base tw-text-gray-600">
      <?= h($message ?? __('error.404.body', 'The page you are looking for could not be found.')) ?>
    </p>
    <a href="/" class="btn btn-primary tw-inline-flex tw-items-center tw-gap-2">
      <span>⬅</span>
      <span><?= __('error.404.cta', 'Back to homepage') ?></span>
    </a>
  </div>
</section>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
