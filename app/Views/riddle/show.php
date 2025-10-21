<?php
ob_start();
?>
<article class="tw-bg-white tw-border tw-rounded-3xl tw-p-8 tw-shadow-sm tw-space-y-6">
  <header class="tw-space-y-3">
    <div class="tw-flex tw-flex-wrap tw-gap-2 tw-text-xs tw-text-gray-500">
      <span class="tw-px-2 tw-py-1 tw-rounded-full tw-bg-indigo-50 tw-text-indigo-600"><?= h($riddle['difficulty']) ?></span>
      <span class="tw-px-2 tw-py-1 tw-rounded-full tw-bg-gray-100 tw-text-gray-600"><?= h($riddle['length']) ?></span>
      <?php foreach ($riddle['categories'] ?? [] as $cat): ?>
        <span class="tw-px-2 tw-py-1 tw-rounded-full tw-bg-slate-100 tw-text-slate-600">#<?= h($cat['name']) ?></span>
      <?php endforeach; ?>
    </div>
    <h1 class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= h($riddle['title']) ?></h1>
  </header>

  <div class="tw-text-lg tw-leading-relaxed tw-text-gray-800">
    <?= nl2br(h($riddle['body'])) ?>
  </div>

  <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-4">
    <button id="revealBtn" class="btn btn-primary">
      <?= __('btn.show_answer', 'Show me the answer') ?>
    </button>
    <div class="tw-flex tw-items-center tw-gap-2">
      <button data-vote="up" class="btn btn-outline-success btn-sm">👍 <?= __('btn.vote_up', 'Upvote') ?></button>
      <button data-vote="down" class="btn btn-outline-danger btn-sm">👎 <?= __('btn.vote_down', 'Downvote') ?></button>
      <span id="score" class="tw-text-sm tw-text-gray-600">
        <?php $total = (int) $riddle['up_votes'] + (int) $riddle['down_votes']; echo $total > 0 ? number_format((int) $riddle['up_votes'] / max(1, $total) * 100, 1) . ' %' : '—'; ?>
      </span>
    </div>
    <div class="tw-flex tw-items-center tw-gap-2 tw-text-sm tw-text-gray-500">
      <span><?= number_format((int) $riddle['views']) ?> <?= __('riddle.views', 'Views') ?></span>
      <button type="button" class="btn btn-outline-secondary btn-sm" data-action="share" data-url="<?= current_url() ?>">
        <?= __('riddle.share', 'Share') ?>
      </button>
    </div>
  </div>

  <div id="answer" class="tw-hidden tw-bg-slate-50 tw-border tw-border-indigo-100 tw-rounded-3xl tw-p-6">
    <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-2"><?= __('riddle.answer_title', 'Answer') ?></h2>
    <div class="tw-text-base tw-text-gray-800 tw-whitespace-pre-line"><?= nl2br(h($riddle['answer'])) ?></div>
  </div>
</article>

<?php if (!empty($related)): ?>
<section class="tw-mt-8 tw-bg-white tw-border tw-rounded-3xl tw-p-6 tw-shadow-sm">
  <h2 class="tw-text-lg tw-font-semibold tw-mb-4"><?= __('riddle.similar', 'Related riddles') ?></h2>
  <div class="tw-grid tw-gap-4 md:tw-grid-cols-2">
    <?php foreach ($related as $item): ?>
      <a href="/riddle/<?= h($item['slug']) ?>" class="tw-block tw-border tw-rounded-2xl tw-p-4 tw-hover:tw-border-indigo-200 tw-transition tw-text-gray-800">
        <div class="tw-text-sm tw-text-gray-500 tw-mb-1"><?= h($item['difficulty']) ?> · <?= number_format((float) ($item['approval'] ?? 0), 1) ?>%</div>
        <div class="tw-font-semibold tw-line-clamp-2"><?= h($item['title']) ?></div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<script>
const revealBtn = document.getElementById('revealBtn');
const answerBox = document.getElementById('answer');
revealBtn?.addEventListener('click', () => {
  const hidden = answerBox.classList.toggle('tw-hidden');
  revealBtn.textContent = hidden ? <?= json_encode(__('btn.show_answer', 'Show me the answer')) ?> : <?= json_encode(__('btn.hide_answer', 'Hide the answer')) ?>;
});

document.querySelectorAll('[data-vote]').forEach(btn => {
  btn.addEventListener('click', async () => {
    const dir = btn.dataset.vote === 'up' ? 1 : -1;
    const res = await fetch('/api/vote', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({id: <?= (int) $riddle['id'] ?>, dir})
    });
    const json = await res.json();
    if (json.ok) {
      document.getElementById('score').textContent = json.percent + ' %';
    }
  });
});

const shareBtn = document.querySelector('[data-action="share"]');
if (shareBtn) {
  shareBtn.addEventListener('click', async () => {
    const shareData = { title: <?= json_encode($riddle['title']) ?>, url: shareBtn.dataset.url };
    if (navigator.share) {
      try { await navigator.share(shareData); } catch(e) { /* ignore */ }
    } else {
      await navigator.clipboard.writeText(shareBtn.dataset.url);
      shareBtn.textContent = <?= json_encode(__('riddle.share_copy', 'Copy link')) ?>;
      setTimeout(() => shareBtn.textContent = <?= json_encode(__('riddle.share', 'Share')) ?>, 2000);
    }
  });
}
</script>
<?php
$content = ob_get_clean();
$title = $riddle['title'];
include __DIR__ . '/../layouts/base.php';
