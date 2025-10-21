<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use App\Models\Answer;
use function App\require_auth;

require_auth(true);
$answers = Answer::forAdminList();

include __DIR__ . '/partials/header.php';
?>
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h4 mb-0">Cevaplar</h1>
    <span class="text-muted small">En güncel 100 kayıt listelenir.</span>
  </div>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>ID</th>
          <th>Kullanıcı</th>
          <th>Görünürlük</th>
          <th>Metin</th>
          <th>Tarih</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($answers as $answer): ?>
          <tr>
            <td><?= $answer['id'] ?></td>
            <td><?= App\h($answer['username']) ?></td>
            <td><?= $answer['is_public'] ? 'Görünür' : 'Gizli' ?></td>
            <td><?= App\h(mb_strimwidth($answer['body'], 0, 120, '…')) ?></td>
            <td><?= App\h($answer['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php include __DIR__ . '/partials/footer.php';
