<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use function App\db;
use function App\require_auth;

$user = require_auth(true);

$pdo = db();
$totalUsers = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalQuestions = (int) $pdo->query('SELECT COUNT(*) FROM questions')->fetchColumn();
$totalAnswers = (int) $pdo->query('SELECT COUNT(*) FROM answers')->fetchColumn();
$latestQuestions = Question::forAdminList();
$latestAnswers = Answer::forAdminList();

include __DIR__ . '/partials/header.php';
?>
  <div class="row g-3">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <h2 class="h6 text-muted mb-2">Kullanıcılar</h2>
          <p class="display-6 fw-bold mb-0"><?= $totalUsers ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <h2 class="h6 text-muted mb-2">Sorular</h2>
          <p class="display-6 fw-bold mb-0"><?= $totalQuestions ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <h2 class="h6 text-muted mb-2">Cevaplar</h2>
          <p class="display-6 fw-bold mb-0"><?= $totalAnswers ?></p>
        </div>
      </div>
    </div>
  </div>

  <div class="row mt-4">
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white">
          <h2 class="h6 mb-0">Son Sorular</h2>
        </div>
        <div class="card-body">
          <?php if (!$latestQuestions): ?>
            <p class="text-muted">Henüz soru bulunmuyor.</p>
          <?php else: ?>
            <div class="list-group list-group-flush">
              <?php foreach (array_slice($latestQuestions, 0, 5) as $question): ?>
                <div class="list-group-item">
                  <div class="small text-muted"><?= App\h($question['created_at']) ?> · <?= App\h($question['to_username']) ?></div>
                  <div><?= App\h($question['body']) ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white">
          <h2 class="h6 mb-0">Son Cevaplar</h2>
        </div>
        <div class="card-body">
          <?php if (!$latestAnswers): ?>
            <p class="text-muted">Henüz cevap bulunmuyor.</p>
          <?php else: ?>
            <div class="list-group list-group-flush">
              <?php foreach (array_slice($latestAnswers, 0, 5) as $answer): ?>
                <div class="list-group-item">
                  <div class="small text-muted"><?= App\h($answer['created_at']) ?> · <?= App\h($answer['username']) ?></div>
                  <div><?= App\h($answer['body']) ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
<?php include __DIR__ . '/partials/footer.php';
