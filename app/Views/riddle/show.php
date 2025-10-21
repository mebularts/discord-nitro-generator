<?php
declare(strict_types=1);
/** @var array $riddle */
/** @var array $related */
/** @var array $stats */
?>
<?php ob_start(); ?>
<article class="tw-rounded-3xl tw-bg-slate-900 tw-border tw-border-slate-800 tw-shadow-xl tw-overflow-hidden">
    <div class="tw-bg-gradient-to-r tw-from-sky-600 tw-to-purple-500 tw-px-6 tw-py-8">
        <h1 class="tw-text-4xl tw-font-bold tw-text-white tw-leading-tight mb-3"><?= htmlspecialchars($riddle['title'], ENT_QUOTES, 'UTF-8') ?></h1>
        <div class="d-flex flex-wrap gap-2 tw-text-sm tw-text-slate-200">
            <span class="badge bg-light text-dark"><?= htmlspecialchars(__('difficulty.' . $riddle['difficulty']), ENT_QUOTES, 'UTF-8') ?></span>
            <span class="badge bg-dark"><?= strtoupper($riddle['locale'] ?? APP_LOCALE) ?></span>
            <span class="badge bg-light text-dark"><?= htmlspecialchars(date('M j, Y', strtotime($riddle['published_at'] ?? $riddle['created_at'])), ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    </div>
    <div class="tw-p-6 tw-space-y-6">
        <div class="tw-text-lg tw-leading-relaxed tw-text-slate-200">
            <?= nl2br(htmlspecialchars($riddle['body'], ENT_QUOTES, 'UTF-8')) ?>
        </div>
        <div class="tw-bg-slate-950 tw-rounded-2xl tw-border tw-border-slate-800 tw-p-5 tw-flex tw-flex-col tw-gap-4" id="answerPanel">
            <button class="btn btn-primary tw-rounded-full tw-self-start" data-action="toggle-answer">
                <?= htmlspecialchars(__('riddles.answer'), ENT_QUOTES, 'UTF-8') ?>
            </button>
            <div class="tw-hidden tw-text-xl tw-font-semibold tw-text-emerald-400" data-answer>
                <?= nl2br(htmlspecialchars($riddle['answer'], ENT_QUOTES, 'UTF-8')) ?>
            </div>
        </div>
        <div class="tw-bg-slate-950 tw-rounded-2xl tw-border tw-border-slate-800 tw-p-5 tw-flex tw-flex-col tw-gap-3" data-riddle-votes
             data-riddle-id="<?= (int) $riddle['id'] ?>">
            <div class="d-flex justify-content-between align-items-center">
                <strong><?= htmlspecialchars(__('riddles.vote_prompt'), ENT_QUOTES, 'UTF-8') ?></strong>
                <span class="tw-text-sm tw-text-slate-400" data-score><?= htmlspecialchars(str_replace([':percent', ':up', ':down'], [$stats['percent'], $stats['upvotes'], $stats['downvotes']], __('votes.score')), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-success flex-grow-1" data-vote="1">👍 <?= htmlspecialchars(__('votes.upvote'), ENT_QUOTES, 'UTF-8') ?></button>
                <button type="button" class="btn btn-danger flex-grow-1" data-vote="-1">👎 <?= htmlspecialchars(__('votes.downvote'), ENT_QUOTES, 'UTF-8') ?></button>
            </div>
            <div class="alert alert-warning tw-hidden" data-error></div>
        </div>
    </div>
</article>

<?php if (!empty($related)): ?>
    <section class="tw-mt-10">
        <h2 class="tw-text-2xl tw-font-semibold tw-text-white mb-4"><?= htmlspecialchars(__('riddles.related'), ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="row g-4">
            <?php foreach ($related as $item): ?>
                <div class="col-12 col-md-4">
                    <a href="/riddles/<?= urlencode($item['slug']) ?>" class="tw-block tw-rounded-2xl tw-bg-slate-900 tw-border tw-border-slate-800 tw-p-5 tw-text-white tw-transition hover:tw-translate-y-0.5">
                        <h3 class="tw-text-lg tw-font-semibold tw-line-clamp-2 mb-2"><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p class="tw-text-slate-400 tw-text-sm tw-line-clamp-3 mb-0"><?= htmlspecialchars(strip_tags($item['body']), ENT_QUOTES, 'UTF-8') ?></p>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
<?php $content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
