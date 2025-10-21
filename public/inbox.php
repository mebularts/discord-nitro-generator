<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use App\Models\Answer;
use App\Models\Question;
use function App\csrf_check;
use function App\csrf_token;
use function App\current_user;
use function App\h;
use function App\redirect;

$user = current_user();
if (!$user) {
    redirect('/login.php');
}

$pageTitle = 'Sorularım';
$pageMeta = [
    'description' => 'SolveClone gelen kutunuzdaki soruları görüntüleyin, yanıtlayın ve gizlilik tercihlerinizi yönetin.',
    'robots' => 'noindex, nofollow',
];
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrf_check();
        $action = $_POST['action'] ?? 'answer';
        $questionId = (int) ($_POST['question_id'] ?? 0);
        $question = null;

        if ($questionId > 0) {
            foreach (Question::inbox((int) $user['id']) as $item) {
                if ((int) $item['id'] === $questionId) {
                    $question = $item;
                    break;
                }
            }
        }

        if (!$question) {
            throw new \RuntimeException('Soru bulunamadı.');
        }

        if ($action === 'answer') {
            $body = $_POST['answer_body'] ?? '';
            $isPublic = isset($_POST['is_public']);
            $existing = Answer::findByQuestion($questionId);
            if ($existing) {
                Answer::updateVisibility((int) $existing['id'], $isPublic);
                $message = 'Cevap görünürlüğü güncellendi.';
            } else {
                Answer::create($questionId, (int) $user['id'], $body, $isPublic);
                $message = 'Cevabınız kaydedildi.';
            }
        } elseif ($action === 'toggle_visibility') {
            $answerId = (int) ($_POST['answer_id'] ?? 0);
            $isPublic = isset($_POST['is_public']);
            Answer::updateVisibility($answerId, $isPublic);
            $message = 'Görünürlük güncellendi.';
        }
    } catch (\Throwable $e) {
        error_log('[inbox] ' . $e->getMessage());
        $error = $e->getMessage();
    }
}

$questions = Question::inbox((int) $user['id']);

require __DIR__ . '/partials/header.php';
?>
  <?php if ($message): ?>
    <div class="alert alert-success" role="alert"><?= h($message) ?></div>
  <?php elseif ($error): ?>
    <div class="alert alert-danger" role="alert"><?= h($error) ?></div>
  <?php endif; ?>

  <div class="row g-4">
    <?php if (!$questions): ?>
      <div class="col-12">
        <div class="empty-state">
          <h2 class="h5 fw-semibold">Henüz soru yok</h2>
          <p class="text-muted">Profilinizi paylaşarak arkadaşlarınızı davet edin.</p>
          <a class="btn btn-outline-primary" href="/profile.php?u=<?= urlencode($user['username']) ?>">Profilimi görüntüle</a>
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($questions as $question): ?>
        <?php $answer = Answer::findByQuestion((int) $question['id']); ?>
        <div class="col-12">
          <article class="card border-0 shadow-sm">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                  <span class="badge bg-secondary me-2">Soru</span>
                  <span class="text-muted small"><?= $question['is_anonymous'] ? 'Anonim' : h($question['from_username'] ?? 'Kullanıcı') ?></span>
                </div>
                <span class="text-muted small"><?= h($question['created_at']) ?></span>
              </div>
              <p class="fs-5 mb-4"><?= nl2br(h($question['body'])) ?></p>

              <?php if ($answer): ?>
                <div class="alert alert-secondary" role="alert">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-semibold">Cevabınız</span>
                    <form method="post" class="d-flex align-items-center gap-2">
                      <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                      <input type="hidden" name="action" value="toggle_visibility">
                      <input type="hidden" name="answer_id" value="<?= (int) $answer['id'] ?>">
                      <input type="hidden" name="question_id" value="<?= (int) $question['id'] ?>">
                      <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="publicToggle<?= (int) $answer['id'] ?>" name="is_public" <?= $answer['is_public'] ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label class="form-check-label" for="publicToggle<?= (int) $answer['id'] ?>">Ana sayfada göster</label>
                      </div>
                    </form>
                  </div>
                  <p class="mb-0"><?= nl2br(h($answer['body'])) ?></p>
                </div>
              <?php else: ?>
                <form method="post" class="mt-4">
                  <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                  <input type="hidden" name="action" value="answer">
                  <input type="hidden" name="question_id" value="<?= (int) $question['id'] ?>">
                  <div class="mb-3">
                    <label class="form-label" for="answer_body<?= (int) $question['id'] ?>">Cevabınız</label>
                    <textarea class="form-control" id="answer_body<?= (int) $question['id'] ?>" name="answer_body" rows="3" required></textarea>
                  </div>
                  <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="answerPublic<?= (int) $question['id'] ?>" name="is_public" checked>
                    <label class="form-check-label" for="answerPublic<?= (int) $question['id'] ?>">Ana sayfada yayınla</label>
                  </div>
                  <button type="submit" class="btn btn-primary">Cevabı Gönder</button>
                </form>
              <?php endif; ?>
            </div>
          </article>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
<?php require __DIR__ . '/partials/footer.php';
