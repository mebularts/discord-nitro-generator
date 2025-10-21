<section aria-labelledby="feed-heading">
    <div class="section-header">
        <h1 id="feed-heading">Topluluk Akışı</h1>
        <p class="section-description">En yeni yanıtları keşfet, sevdiğin profillere soru sor.</p>
    </div>
    <?php if (empty($feed['items'])): ?>
        <div class="empty-state" role="status">
            <h2>Henüz cevap yok.</h2>
            <p>İlk soruyu sorarak akışı başlatabilirsin.</p>
            <a class="button" href="/register">Topluluğa Katıl</a>
        </div>
    <?php else: ?>
        <ul class="card-list">
            <?php foreach ($feed['items'] as $answer): ?>
                <li class="card" tabindex="0">
                    <header class="card-header" style="--accent-color: <?= App\Support\Helpers::escape($answer['profile_color'] ?? '#6366f1'); ?>">
                        <strong><?= App\Support\Helpers::escape($answer['username']); ?></strong>
                        <span class="card-meta"><?= date('d.m.Y H:i', strtotime($answer['created_at'])); ?></span>
                    </header>
                    <div class="card-body">
                        <p class="question-label">Soru</p>
                        <p><?= nl2br(App\Support\Helpers::escape($answer['question_body'] ?? '')); ?></p>
                        <p class="answer-label">Cevap</p>
                        <p><?= nl2br(App\Support\Helpers::escape($answer['body'])); ?></p>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php if ($feed['total'] > $perPage): ?>
            <nav class="pagination" aria-label="Sayfalama">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1; ?>" class="button">Önceki</a>
                <?php endif; ?>
                <span>Sayfa <?= $page; ?></span>
                <?php if ($page * $perPage < $feed['total']): ?>
                    <a href="?page=<?= $page + 1; ?>" class="button">Sonraki</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>
