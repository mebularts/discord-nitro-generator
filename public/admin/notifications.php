<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use App\Models\Notification;
use function App\require_auth;

require_auth(true);
$notifications = Notification::latest();

include __DIR__ . '/partials/header.php';
?>
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h4 mb-0">Bildirimler</h1>
    <span class="text-muted small">Son 100 bildirim listelenir.</span>
  </div>
  <div class="list-group shadow-sm">
    <?php foreach ($notifications as $notification): ?>
      <div class="list-group-item">
        <div class="d-flex justify-content-between align-items-center">
          <span class="badge bg-primary text-uppercase"><?= App\h($notification['type']) ?></span>
          <span class="text-muted small"><?= App\h($notification['created_at']) ?></span>
        </div>
        <pre class="mt-2 mb-0 small bg-light p-2 rounded"><?= App\h($notification['data_json']) ?></pre>
      </div>
    <?php endforeach; ?>
  </div>
<?php include __DIR__ . '/partials/footer.php';
