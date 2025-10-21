<?php
declare(strict_types=1);
/** @var array $filters */
/** @var array $categories */
/** @var array $pagination */
?>
<?php ob_start(); ?>
<section class="tw-grid tw-gap-6">
    <div class="tw-rounded-3xl tw-bg-slate-900 tw-p-6 tw-shadow-lg tw-border tw-border-slate-800 tw-overflow-hidden">
        <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center md:tw-justify-between tw-gap-4">
            <div>
                <h1 class="tw-text-3xl tw-font-bold tw-text-white mb-2"><?= htmlspecialchars(__('home.heading'), ENT_QUOTES, 'UTF-8') ?></h1>
                <p class="tw-text-slate-300 mb-0"><?= htmlspecialchars(__('home.subtitle'), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <div class="tw-flex tw-gap-3">
                <a href="/admin" class="btn btn-outline-light btn-sm">Admin</a>
                <a href="/manifest.webmanifest" class="btn btn-light btn-sm">PWA manifest</a>
            </div>
        </div>
    </div>

    <div class="tw-rounded-3xl tw-bg-slate-900 tw-p-6 tw-border tw-border-slate-800 tw-shadow-lg">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label tw-text-xs tw-uppercase tw-text-slate-400"><?= htmlspecialchars(__('home.category'), ENT_QUOTES, 'UTF-8') ?></label>
                <select name="category" class="form-select">
                    <option value="">—</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['slug'], ENT_QUOTES, 'UTF-8') ?>" <?= ($filters['category'] ?? '') === $category['slug'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-4">
                <label class="form-label tw-text-xs tw-uppercase tw-text-slate-400"><?= htmlspecialchars(__('home.difficulty'), ENT_QUOTES, 'UTF-8') ?></label>
                <select name="difficulty" class="form-select">
                    <option value="">—</option>
                    <?php foreach (['easy', 'medium', 'hard'] as $difficulty): ?>
                        <option value="<?= $difficulty ?>" <?= ($filters['difficulty'] ?? '') === $difficulty ? 'selected' : '' ?>><?= htmlspecialchars(__('difficulty.' . $difficulty), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-4">
                <label class="form-label tw-text-xs tw-uppercase tw-text-slate-400"><?= htmlspecialchars(__('home.length'), ENT_QUOTES, 'UTF-8') ?></label>
                <select name="length" class="form-select">
                    <option value="">—</option>
                    <?php foreach (['short', 'medium', 'long'] as $length): ?>
                        <option value="<?= $length ?>" <?= ($filters['length'] ?? '') === $length ? 'selected' : '' ?>><?= htmlspecialchars(__('length.' . $length), ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <button type="submit" class="btn btn-primary w-100"><?= htmlspecialchars(__('home.apply'), ENT_QUOTES, 'UTF-8') ?></button>
            </div>
            <div class="col-6 col-md-2">
                <a class="btn btn-outline-secondary w-100" href="/"><?= htmlspecialchars(__('home.reset'), ENT_QUOTES, 'UTF-8') ?></a>
            </div>
        </form>
    </div>

    <div class="row g-4">
        <?php if (empty($pagination['items'])): ?>
            <div class="col-12">
                <div class="tw-rounded-2xl tw-bg-slate-900 tw-border tw-border-slate-800 tw-p-6 tw-text-center tw-text-slate-300">
                    <?= htmlspecialchars(__('home.no_results'), ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($pagination['items'] as $item): ?>
                <div class="col-12 col-md-6 col-xl-4">
                    <article class="tw-rounded-3xl tw-bg-slate-900 tw-border tw-border-slate-800 tw-h-full tw-flex tw-flex-col tw-p-5 tw-gap-4 tw-shadow-lg">
                        <div>
                            <h2 class="tw-text-xl tw-font-semibold tw-text-white tw-line-clamp-2"><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                            <p class="tw-text-slate-400 tw-text-sm tw-line-clamp-3"><?= htmlspecialchars(strip_tags($item['body']), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <div class="d-flex tw-flex-wrap tw-gap-2">
                            <span class="badge bg-sky-500 tw-text-xs"><?= htmlspecialchars(__('difficulty.' . $item['difficulty']), ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="badge bg-purple-500 tw-text-xs"><?= strtoupper($item['locale'] ?? APP_LOCALE) ?></span>
                        </div>
                        <a href="/riddles/<?= urlencode($item['slug']) ?>" class="btn btn-light tw-font-semibold tw-shadow-md tw-rounded-full tw-py-2 tw-transition tw-duration-150 hover:tw-translate-y-0.5">
                            <?= htmlspecialchars(__('home.see_details'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </article>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if (($pagination['totalPages'] ?? 1) > 1): ?>
        <nav class="d-flex justify-content-between align-items-center tw-bg-slate-900 tw-rounded-2xl tw-border tw-border-slate-800 tw-p-4">
            <span class="tw-text-sm tw-text-slate-400"><?= htmlspecialchars(str_replace([':current', ':total'], [$pagination['current'], $pagination['totalPages']], __('pagination.page')), ENT_QUOTES, 'UTF-8') ?></span>
            <div class="d-flex gap-2">
                <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
                    <a class="btn btn-sm <?= $i === $pagination['current'] ? 'btn-primary' : 'btn-outline-secondary' ?>" href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>">#<?= $i ?></a>
                <?php endfor; ?>
            </div>
        </nav>
    <?php endif; ?>
</section>
<?php $content = ob_get_clean();
include __DIR__ . '/layouts/base.php';
