<?php
declare(strict_types=1);

$pageTitle = 'Ana Sayfa';

require_once __DIR__ . '/../app/bootstrap.php';

use App\Models\Answer;
use function App\h;

$answers = Answer::latestPublicFeed();

require __DIR__ . '/partials/header.php';
?>
  <section class="mb-5">
    <div class="bg-gradient rounded-4 p-5 text-white" style="background: linear-gradient(135deg, #2563eb, #7c3aed);">
      <div class="row align-items-center g-4">
        <div class="col-lg-7">
          <h1 class="display-5 fw-bold">Bulmacaları çöz, profilini renklendir.</h1>
          <p class="lead">SolveClone topluluğuna katıl, profilini özelleştir ve arkadaşlarına sorular gönder. Cevaplarını ana sayfada paylaşırken kontrol sende.</p>
          <div class="d-flex gap-3">
            <a href="/register.php" class="btn btn-light btn-lg">Hemen Başla</a>
            <a href="#feed" class="btn btn-outline-light btn-lg">Son Cevaplar</a>
          </div>
        </div>
        <div class="col-lg-5 text-lg-end">
          <div class="card bg-white text-dark shadow-lg border-0">
            <div class="card-body">
              <h2 class="h5 fw-semibold mb-3">PWA + Modern Yönetim</h2>
              <ul class="list-unstyled mb-0">
                <li class="d-flex align-items-center mb-2">✅ Çevrimdışı destek</li>
                <li class="d-flex align-items-center mb-2">✅ Profil kişiselleştirme</li>
                <li class="d-flex align-items-center mb-2">✅ Sorular için bildirimler</li>
                <li class="d-flex align-items-center">✅ Güvenli yönetim paneli</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="feed" aria-labelledby="feedTitle">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 id="feedTitle" class="h3 fw-semibold">Son Cevaplar</h2>
      <p class="text-muted mb-0">En güncel 50 cevap.</p>
    </div>
    <?php if (!$answers): ?>
      <div class="empty-state">
        <h3 class="h5 fw-semibold mb-2">Henüz cevap yok</h3>
        <p class="text-muted">İlk cevabı vermek için profil oluşturup soruları yanıtlayın.</p>
        <a class="btn btn-primary" href="/register.php">Hemen üye ol</a>
      </div>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($answers as $answer): ?>
          <div class="col-12 col-md-6">
            <article class="feed-card p-4 h-100" tabindex="0">
              <div class="d-flex align-items-center gap-3 mb-3">
                <?php if ($answer['avatar']): ?>
                  <img src="<?= h($answer['avatar']) ?>" alt="<?= h($answer['user_username']) ?> avatarı" class="rounded-circle" width="56" height="56">
                <?php else: ?>
                  <div class="avatar-placeholder" style="width:56px;height:56px;">
                    <?= strtoupper(substr($answer['user_username'], 0, 1)) ?>
                  </div>
                <?php endif; ?>
                <div>
                  <a class="fw-semibold text-decoration-none" href="/profile.php?u=<?= urlencode($answer['user_username']) ?>">
                    <?= h($answer['user_username']) ?>
                  </a>
                  <div class="text-muted small"><?= h($answer['created_at']) ?></div>
                </div>
              </div>
              <p class="text-muted small mb-2">Soruldu:</p>
              <p class="fw-semibold"><?= h($answer['question_body']) ?></p>
              <p class="text-muted small mb-2">Yanıt:</p>
              <p><?= nl2br(h($answer['body'])) ?></p>
              <div class="mt-3">
                <a class="btn btn-outline-primary btn-sm" href="/profile.php?u=<?= urlencode($answer['user_username']) ?>">Profiline git</a>
              </div>
            </article>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
<?php require __DIR__ . '/partials/footer.php';
