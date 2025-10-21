<section class="inbox" aria-labelledby="inbox-heading">
    <h1 id="inbox-heading">Gelen Soruların</h1>
    <?php if (empty($items['items'])): ?>
        <div class="empty-state" role="status">
            <h2>Henüz soru yok.</h2>
            <p>Profilini paylaşarak soru toplamaya başlayabilirsin.</p>
        </div>
    <?php else: ?>
        <ul class="card-list">
            <?php foreach ($items['items'] as $question): ?>
                <li class="card">
                    <header class="card-header">
                        <span class="card-meta"><?= date('d.m.Y H:i', strtotime($question['created_at'])); ?></span>
                        <span class="badge">
                            <?= $question['is_anonymous'] ? 'Anonim' : App\Support\Helpers::escape($question['from_username'] ?? ''); ?>
                        </span>
                    </header>
                    <div class="card-body">
                        <p><?= nl2br(App\Support\Helpers::escape($question['body'])); ?></p>
                        <form method="post" action="/answers" class="form inline-form" data-answer-form>
                            <input type="hidden" name="_token" value="<?= $csrf_token; ?>">
                            <input type="hidden" name="question_id" value="<?= (int) $question['id']; ?>">
                            <label for="answer-<?= (int) $question['id']; ?>">Cevabın</label>
                            <textarea id="answer-<?= (int) $question['id']; ?>" name="body" maxlength="3000" required></textarea>
                            <label class="switch">
                                <input type="checkbox" name="is_public" value="1" checked>
                                <span>Herkese açık</span>
                            </label>
                            <button type="submit" class="button button-primary">Cevapla</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
