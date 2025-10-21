<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use App\Models\Question;
use App\Models\User;
use function App\csrf_check;
use function App\csrf_token;
use function App\current_user;
use function App\h;
use function App\redirect;

$username = $_GET['u'] ?? '';
$profile = User::findByUsername($username);

if (!$profile) {
    http_response_code(404);
    exit('Profil bulunamadı.');
}

$pageTitle = $profile['username'] . ' Profili';
$description = '';
if (!empty($profile['bio'])) {
    $plainBio = trim(strip_tags($profile['bio']));
    if ($plainBio !== '') {
        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            $description = mb_strlen($plainBio, 'UTF-8') > 155 ? mb_substr($plainBio, 0, 155, 'UTF-8') . '…' : $plainBio;
        } else {
            $description = strlen($plainBio) > 155 ? substr($plainBio, 0, 155) . '…' : $plainBio;
        }
    }
}
$pageMeta = [
    'description' => $description !== '' ? $profile['username'] . ': ' . $description : $pageTitle . ' | SolveClone topluluğunda profil detayları.',
];
$current = current_user();
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question_body'])) {
    if (!$current) {
        redirect('/login.php');
    }

    try {
        csrf_check();
        $isAnonymous = isset($_POST['is_anonymous']);
        Question::create((int) $current['id'], (int) $profile['id'], $_POST['question_body'] ?? '', $isAnonymous);
        $message = 'Sorun gönderildi!';
    } catch (\Throwable $e) {
        $error = $e->getMessage();
    }
}

$questions = Question::latestForProfile((int) $profile['id']);

require __DIR__ . '/partials/header.php';
?>
  <div class="profile-banner mb-4" style="background: linear-gradient(135deg, <?= h($profile['profile_color'] ?? '#2563eb') ?>, rgba(79,70,229,0.8));">
    <div class="d-flex flex-column flex-md-row align-items-md-end align-items-start gap-3 p-4 text-white">
      <?php if ($profile['avatar']): ?>
        <img src="<?= h($profile['avatar']) ?>" class="profile-avatar" alt="<?= h($profile['username']) ?> avatarı">
      <?php else: ?>
        <div class="avatar-placeholder"><?= strtoupper(substr($profile['username'], 0, 1)) ?></div>
      <?php endif; ?>
      <div>
        <h1 class="h2 fw-bold mb-1"><?= h($profile['username']) ?></h1>
        <?php if ($profile['bio']): ?>
          <p class="mb-2 lead"><?= nl2br(h($profile['bio'])) ?></p>
        <?php endif; ?>
        <div class="d-flex gap-3 flex-wrap text-white-50 small">
          <?php if ($profile['social_links']): ?>
            <?php foreach (json_decode($profile['social_links'], true, 512, JSON_THROW_ON_ERROR) as $platform => $url): ?>
              <a href="<?= h($url) ?>" class="text-white" target="_blank" rel="noopener">#<?= h($platform) ?></a>
            <?php endforeach; ?>
          <?php endif; ?>
          <span class="visibility-indicator bg-light text-dark">
            <?= $profile['questions_public'] ? 'Ana sayfada görünür' : 'Ana sayfada gizli' ?>
          </span>
        </div>
      </div>
    </div>
  </div>

  <?php if ($message): ?>
    <div class="alert alert-success" role="alert"><?= h($message) ?></div>
  <?php elseif ($error): ?>
    <div class="alert alert-danger" role="alert"><?= h($error) ?></div>
  <?php endif; ?>

  <?php if ($current && $current['id'] !== $profile['id']): ?>
    <div class="d-flex justify-content-end mb-4">
      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#askQuestionModal">
        <span class="me-2" aria-hidden="true">📝</span> Soru Sor
      </button>
    </div>
  <?php elseif (!$current): ?>
    <div class="alert alert-info">Soru sormak için <a href="/login.php">giriş yapın</a>.</div>
  <?php endif; ?>

  <div class="row g-4">
    <div class="col-lg-7">
      <section aria-labelledby="answersTitle" class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <h2 class="h5 fw-semibold" id="answersTitle">Yayınlanan Cevaplar</h2>
          <?php
          $publicAnswers = array_filter($questions, static function ($item): bool {
              return $item['answer_id'] && (int) $item['is_public'] === 1;
          });
          ?>
          <?php if (!$publicAnswers): ?>
            <div class="text-muted">Bu kullanıcı henüz herkese açık cevap paylaşmadı.</div>
          <?php else: ?>
            <?php foreach ($publicAnswers as $answer): ?>
              <article class="mt-3">
                <header class="mb-2 text-muted small">Soru: <?= h($answer['body']) ?></header>
                <div><?= nl2br(h($answer['answer_body'])) ?></div>
                <footer class="text-muted small mt-1">Yayınlanma: <?= h($answer['answer_created_at']) ?></footer>
              </article>
              <hr>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </section>
    </div>
    <div class="col-lg-5">
      <section aria-labelledby="questionsTitle" class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <h2 class="h5 fw-semibold" id="questionsTitle">Sorular</h2>
          <?php if (!$questions): ?>
            <div class="text-muted">Henüz soru yok.</div>
          <?php else: ?>
            <div class="list-group list-group-flush">
              <?php foreach ($questions as $question): ?>
                <div class="list-group-item">
                  <div class="text-muted small mb-1">
                    <?= $question['is_anonymous'] ? 'Anonim' : h($question['from_user_id'] ? ($question['from_username'] ?? 'Kullanıcı') : 'Anonim') ?> • <?= h($question['created_at']) ?>
                  </div>
                  <p class="mb-1"><?= h($question['body']) ?></p>
                  <?php if ($question['answer_id']): ?>
                    <span class="badge bg-success">Cevaplandı</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Cevap bekliyor</span>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </div>
  <?php if ($current && $current['id'] !== $profile['id']): ?>
    <button type="button" class="btn btn-primary btn-lg d-md-none floating-action" data-bs-toggle="modal" data-bs-target="#askQuestionModal" aria-label="<?= h($profile['username']) ?> kullanıcısına soru sor">
      <span aria-hidden="true">Soru Sor</span>
    </button>
    <div class="modal fade" id="askQuestionModal" tabindex="-1" aria-labelledby="askQuestionModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h2 class="modal-title h5" id="askQuestionModalLabel"><?= h($profile['username']) ?> kullanıcısına soru sor</h2>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
          </div>
          <form method="post" class="needs-validation" novalidate>
            <div class="modal-body">
              <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
              <div class="mb-3">
                <label class="form-label" for="question_body">Soru</label>
                <textarea class="form-control" id="question_body" name="question_body" rows="4" required maxlength="500"></textarea>
                <div class="form-text">En fazla 500 karakter.</div>
              </div>
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="anonymousToggle" name="is_anonymous">
                <label class="form-check-label" for="anonymousToggle">Soruyu anonim gönder</label>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Vazgeç</button>
              <button type="submit" class="btn btn-primary">Soruyu Gönder</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>
<?php require __DIR__ . '/partials/footer.php';
