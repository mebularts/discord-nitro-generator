<section class="notifications" aria-labelledby="notifications-heading">
    <div class="section-header">
        <h1 id="notifications-heading">Bildirimler</h1>
        <form method="post" action="/notifications/read" class="inline-form">
            <input type="hidden" name="_token" value="<?= $csrf_token; ?>">
            <button type="submit" class="button">Tümünü okundu işaretle</button>
        </form>
    </div>
    <?php if (empty($notifications)): ?>
        <div class="empty-state" role="status">
            <h2>Hiç bildirimin yok.</h2>
            <p>Sorular veya cevaplar geldiğinde burada göreceksin.</p>
        </div>
    <?php else: ?>
        <ul class="timeline">
            <?php foreach ($notifications as $notification): $data = json_decode($notification['data_json'], true) ?: []; ?>
                <li class="timeline-item <?= $notification['is_read'] ? 'read' : 'unread'; ?>">
                    <span class="timeline-time"><?= date('d.m.Y H:i', strtotime($notification['created_at'])); ?></span>
                    <div class="timeline-content">
                        <?php if ($notification['type'] === 'new_question'): ?>
                            <p>Sana yeni bir soru geldi.</p>
                        <?php elseif ($notification['type'] === 'new_answer'): ?>
                            <p>Bir soruya yeni cevap var.</p>
                        <?php else: ?>
                            <p>Sistem bildirimi.</p>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
