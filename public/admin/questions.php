<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use App\Models\Question;
use function App\require_auth;

require_auth(true);
$questions = Question::forAdminList();

include __DIR__ . '/partials/header.php';
?>
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h4 mb-0">Sorular</h1>
    <span class="text-muted small">En güncel 100 kayıt listelenir.</span>
  </div>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>ID</th>
          <th>Gönderen</th>
          <th>Hedef</th>
          <th>Anonim mi?</th>
          <th>Metin</th>
          <th>Tarih</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($questions as $question): ?>
          <tr>
            <td><?= $question['id'] ?></td>
            <td><?= App\h($question['from_username'] ?? 'Anonim') ?></td>
            <td><?= App\h($question['to_username']) ?></td>
            <td><?= $question['is_anonymous'] ? 'Evet' : 'Hayır' ?></td>
            <td><?= App\h(mb_strimwidth($question['body'], 0, 120, '…')) ?></td>
            <td><?= App\h($question['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php include __DIR__ . '/partials/footer.php';
