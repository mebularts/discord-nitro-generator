<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use App\Models\User;
use function App\require_auth;

require_auth(true);
$users = User::forAdminList();

include __DIR__ . '/partials/header.php';
?>
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h4 mb-0">Kullanıcılar</h1>
    <span class="text-muted small">En fazla 100 kayıt gösterilir.</span>
  </div>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>ID</th>
          <th>Kullanıcı</th>
          <th>E-posta</th>
          <th>Rol</th>
          <th>Görünürlük</th>
          <th>Oluşturulma</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $user): ?>
          <tr>
            <td><?= $user['id'] ?></td>
            <td><?= App\h($user['username']) ?></td>
            <td><?= App\h($user['email']) ?></td>
            <td><?= App\h($user['role']) ?></td>
            <td><?= $user['questions_public'] ? 'Açık' : 'Kapalı' ?></td>
            <td><?= App\h($user['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php include __DIR__ . '/partials/footer.php';
