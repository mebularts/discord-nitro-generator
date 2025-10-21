<section class="profile" aria-labelledby="profile-heading">
    <div class="profile-header" style="--profile-color: <?= App\Support\Helpers::escape($profile['profile_color'] ?? '#1f2937'); ?>">
        <div class="profile-avatar" aria-hidden="true"><?= strtoupper(substr($profile['username'], 0, 1)); ?></div>
        <div class="profile-meta">
            <h1 id="profile-heading">@<?= App\Support\Helpers::escape($profile['username']); ?></h1>
            <?php if (!empty($profile['name'])): ?>
                <p class="profile-name"><?= App\Support\Helpers::escape($profile['name']); ?></p>
            <?php endif; ?>
            <?php if (!empty($profile['bio'])): ?>
                <p class="profile-bio"><?= nl2br(App\Support\Helpers::escape($profile['bio'])); ?></p>
            <?php endif; ?>
            <button class="button" type="button" data-open-question-modal data-user="<?= (int) $profile['id']; ?>">Soru Sor</button>
        </div>
    </div>

    <div class="tab-group" role="tablist">
        <button class="tab" role="tab" aria-selected="true">Cevaplar</button>
        <button class="tab" role="tab" aria-selected="false" disabled>Hakkında</button>
    </div>

    <?php if (empty($answers['items'])): ?>
        <div class="empty-state" role="status">
            <h2>Henüz cevap yok.</h2>
            <p>Bu profil yeni olabilir. Bir soru göndererek ilk cevabı sen al.</p>
        </div>
    <?php else: ?>
        <ul class="card-list">
            <?php foreach ($answers['items'] as $item): ?>
                <li class="card">
                    <header class="card-header">
                        <span class="card-meta"><?= date('d.m.Y', strtotime($item['created_at'])); ?></span>
                    </header>
                    <div class="card-body">
                        <p class="question-label">Soru</p>
                        <p><?= nl2br(App\Support\Helpers::escape($item['question_body'])); ?></p>
                        <p class="answer-label">Cevap</p>
                        <p><?= nl2br(App\Support\Helpers::escape($item['body'])); ?></p>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
<div class="modal" data-question-modal hidden>
    <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="ask-title">
        <header class="modal-header">
            <h2 id="ask-title">Soru Sor</h2>
            <button class="icon-button" type="button" data-close-modal aria-label="Kapat">✕</button>
        </header>
        <form method="post" action="/questions" data-question-form>
            <input type="hidden" name="_token" value="<?= $csrf_token; ?>">
            <input type="hidden" name="to_user_id" value="<?= (int) $profile['id']; ?>">
            <label for="question-body">Sorunu yaz</label>
            <textarea id="question-body" name="body" maxlength="2000" required></textarea>
            <label class="switch">
                <input type="checkbox" name="is_anonymous" value="1">
                <span>Anonim gönder</span>
            </label>
            <button type="submit" class="button button-primary">Gönder</button>
        </form>
        <p class="form-helper">Formu gönderdiğinde alıcıya bildirim gidecek.</p>
    </div>
</div>
