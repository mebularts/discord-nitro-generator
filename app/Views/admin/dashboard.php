<section class="admin" aria-labelledby="admin-heading">
    <h1 id="admin-heading">Yönetim Paneli</h1>
    <div class="grid">
        <article class="stat-card">
            <h2>Toplam Kullanıcı</h2>
            <p><?= (int) ($metrics['total_users'] ?? 0); ?></p>
        </article>
        <article class="stat-card">
            <h2>Son 24s Sorular</h2>
            <p><?= (int) ($metrics['questions_24h'] ?? 0); ?></p>
        </article>
        <article class="stat-card">
            <h2>Son 24s Cevaplar</h2>
            <p><?= (int) ($metrics['answers_24h'] ?? 0); ?></p>
        </article>
        <article class="stat-card">
            <h2>Okunmamış Bildirim</h2>
            <p><?= (int) ($metrics['unread_notifications'] ?? 0); ?></p>
        </article>
    </div>
    <p class="form-helper">Detaylı yönetim listeleri ilerleyen sürümlerde eklenecek.</p>
</section>
