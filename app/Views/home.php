<?php
ob_start();
?>
<section class="tw-bg-gradient-to-r tw-from-indigo-500 tw-to-purple-500 tw-rounded-3xl tw-text-white tw-p-8 tw-flex tw-flex-col lg:tw-flex-row tw-gap-6">
  <div class="tw-flex-1">
    <h1 class="tw-text-3xl tw-font-bold tw-mb-3"><?= __('home.hero_title', 'Sharpen your mind with fresh riddles every day') ?></h1>
    <p class="tw-text-base tw-text-indigo-100 tw-max-w-2xl">
      <?= __('home.hero_subtitle', 'SolveClone delivers logic, math and word puzzles with instant translations and an installable app experience.') ?>
    </p>
    <div class="tw-flex tw-flex-wrap tw-gap-4 tw-mt-6">
      <div class="tw-bg-white/10 tw-rounded-2xl tw-px-4 tw-py-3">
        <div class="tw-text-xs tw-uppercase tw-tracking-wide tw-text-indigo-200"><?= __('home.stats_riddles', 'Riddles') ?></div>
        <div class="tw-text-2xl tw-font-semibold"><?= number_format((int) ($stats['riddles'] ?? 0)) ?></div>
      </div>
      <div class="tw-bg-white/10 tw-rounded-2xl tw-px-4 tw-py-3">
        <div class="tw-text-xs tw-uppercase tw-tracking-wide tw-text-indigo-200"><?= __('home.stats_users', 'Creators') ?></div>
        <div class="tw-text-2xl tw-font-semibold"><?= number_format((int) ($stats['users'] ?? 0)) ?></div>
      </div>
      <div class="tw-bg-white/10 tw-rounded-2xl tw-px-4 tw-py-3">
        <div class="tw-text-xs tw-uppercase tw-tracking-wide tw-text-indigo-200"><?= __('home.stats_votes', 'Votes cast') ?></div>
        <div class="tw-text-2xl tw-font-semibold"><?= number_format((int) ($stats['votes'] ?? 0)) ?></div>
      </div>
    </div>
  </div>
  <div class="tw-flex tw-flex-col tw-gap-4 tw-justify-center">
    <a href="/app" class="btn btn-light tw-font-semibold tw-shadow-sm"><?= __('nav.app', 'Install App') ?></a>
    <a href="/new" class="btn btn-outline-light"><?= __('home.view_all', 'View all riddles') ?></a>
  </div>
</section>

<section class="tw-mt-8 tw-bg-white tw-border tw-rounded-3xl tw-p-6 tw-shadow-sm">
  <form method="get" class="tw-grid tw-gap-4 md:tw-grid-cols-2 lg:tw-grid-cols-6">
    <div class="tw-flex tw-flex-col">
      <label class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase" for="filter-search"><?= __('home.search_placeholder', 'Search riddles…') ?></label>
      <input id="filter-search" type="search" name="q" value="<?= h($filters['q'] ?? '') ?>" class="form-control">
    </div>
    <div class="tw-flex tw-flex-col">
      <label class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase" for="filter-difficulty"><?= __('label.difficulty', 'Difficulty') ?></label>
      <select id="filter-difficulty" name="difficulty" class="form-select">
        <option value=""><?= __('btn.reset', 'Reset') ?></option>
        <?php foreach (['easy','medium','hard','difficult'] as $diff): ?>
          <option value="<?= $diff ?>" <?= ($filters['difficulty'] ?? '') === $diff ? 'selected' : '' ?>><?= ucfirst($diff) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="tw-flex tw-flex-col">
      <label class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase" for="filter-length"><?= __('label.length', 'Length') ?></label>
      <select id="filter-length" name="length" class="form-select">
        <option value=""><?= __('btn.reset', 'Reset') ?></option>
        <?php foreach (['short','long','simple'] as $len): ?>
          <option value="<?= $len ?>" <?= ($filters['length'] ?? '') === $len ? 'selected' : '' ?>><?= ucfirst($len) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="tw-flex tw-flex-col">
      <label class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase" for="filter-category"><?= __('label.category', 'Category') ?></label>
      <select id="filter-category" name="category" class="form-select">
        <option value=""><?= __('btn.reset', 'Reset') ?></option>
        <?php foreach (($categories ?? []) as $cat): ?>
          <option value="<?= h($cat['slug']) ?>" <?= ($filters['category'] ?? '') === $cat['slug'] ? 'selected' : '' ?>><?= h($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="tw-flex tw-flex-col">
      <label class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase" for="filter-sort"><?= __('label.sort', 'Sort by') ?></label>
      <select id="filter-sort" name="sort" class="form-select">
        <option value="pop" <?= ($filters['sort'] ?? '') === 'pop' ? 'selected' : '' ?>><?= __('sort.popular', 'Top rated') ?></option>
        <option value="new" <?= ($filters['sort'] ?? '') === 'new' ? 'selected' : '' ?>><?= __('sort.new', 'Newest') ?></option>
      </select>
    </div>
    <div class="tw-flex tw-items-end tw-gap-2">
      <button class="btn btn-primary tw-flex-1"><?= __('btn.filter', 'Filter') ?></button>
      <a href="/" class="btn btn-outline-secondary tw-flex-1"><?= __('btn.reset', 'Reset') ?></a>
    </div>
  </form>
</section>

<section class="tw-mt-8 tw-space-y-6">
  <?php if (empty($items)): ?>
    <div class="tw-bg-white tw-border tw-rounded-3xl tw-p-10 tw-text-center tw-text-gray-600">
      <?= __('home.no_results', 'No riddles match your filters yet. Try adjusting the criteria.') ?>
    </div>
  <?php else: ?>
    <div class="tw-grid tw-gap-4 md:tw-grid-cols-2">
      <?php foreach ($items as $r): ?>
        <article class="tw-bg-white tw-border tw-rounded-3xl tw-p-5 tw-flex tw-flex-col tw-gap-4 tw-shadow-xs">
          <header>
            <div class="tw-flex tw-items-center tw-gap-2 tw-text-xs tw-text-gray-500">
              <span class="tw-px-2 tw-py-1 tw-rounded-full tw-bg-indigo-50 tw-text-indigo-600"><?= h($r['difficulty']) ?></span>
              <span class="tw-px-2 tw-py-1 tw-rounded-full tw-bg-gray-100 tw-text-gray-600"><?= h($r['length']) ?></span>
              <?php if (!empty($r['categories'])): ?>
                <?php foreach ($r['categories'] as $cat): ?>
                  <span class="tw-px-2 tw-py-1 tw-rounded-full tw-bg-slate-100 tw-text-slate-600">#<?= h($cat['name']) ?></span>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
            <h2 class="tw-text-xl tw-font-semibold tw-mt-3">
              <a class="tw-text-gray-900 hover:tw-text-indigo-600" href="/riddle/<?= h($r['slug']) ?>"><?= h($r['title']) ?></a>
            </h2>
          </header>
          <p class="tw-text-sm tw-text-gray-700 tw-line-clamp-4"><?= nl2br(h(mb_substr($r['body'], 0, 220))) ?><?= mb_strlen($r['body']) > 220 ? '…' : '' ?></p>
          <div class="tw-flex tw-items-center tw-justify-between tw-text-xs tw-text-gray-500">
            <span><?= __('riddle.rating', 'Approval') ?>: <?= number_format((float) ($r['approval'] ?? 0), 1) ?>%</span>
            <a href="/riddle/<?= h($r['slug']) ?>" class="btn btn-sm btn-outline-primary"><?= __('btn.solve', 'Solve') ?></a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php paginator($page, $per, $total); ?>

<section class="tw-mt-10 tw-grid tw-gap-6 lg:tw-grid-cols-2">
  <div class="tw-bg-white tw-border tw-rounded-3xl tw-p-6 tw-shadow-sm">
    <h3 class="tw-text-lg tw-font-semibold tw-mb-4"><?= __('home.featured_trending', 'Trending now') ?></h3>
    <ul class="tw-space-y-3">
      <?php foreach (($trending ?? []) as $item): ?>
        <li class="tw-flex tw-justify-between tw-items-start">
          <a class="tw-text-sm tw-font-medium tw-text-gray-900 hover:tw-text-indigo-600" href="/riddle/<?= h($item['slug']) ?>"><?= h($item['title']) ?></a>
          <span class="tw-text-xs tw-text-gray-500"><?= number_format((float) ($item['approval'] ?? 0), 1) ?>%</span>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <div class="tw-bg-white tw-border tw-rounded-3xl tw-p-6 tw-shadow-sm">
    <h3 class="tw-text-lg tw-font-semibold tw-mb-4"><?= __('home.featured_recent', 'Recently published') ?></h3>
    <ul class="tw-space-y-3">
      <?php foreach (($recent ?? []) as $item): ?>
        <li>
          <a class="tw-text-sm tw-font-medium tw-text-gray-900 hover:tw-text-indigo-600" href="/riddle/<?= h($item['slug']) ?>"><?= h($item['title']) ?></a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/base.php';
