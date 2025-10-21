<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use function App\csrf_check;
use function App\csrf_token;
use function App\db;
use function App\h;
use function App\require_auth;

require_auth(true);
$pdo = db();
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrf_check();
        $settings = [
            'site_name' => trim($_POST['site_name'] ?? 'SolveClone'),
            'default_locale' => trim($_POST['default_locale'] ?? 'tr'),
            'theme' => trim($_POST['theme'] ?? 'system'),
        ];

        foreach ($settings as $key => $value) {
            $stmt = $pdo->prepare('REPLACE INTO settings (`key`, value) VALUES (:key, :value)');
            $stmt->execute(['key' => $key, 'value' => $value]);
        }

        $message = 'Ayarlar başarıyla güncellendi.';
    } catch (Throwable $e) {
        error_log('[settings] ' . $e->getMessage());
        $error = $e->getMessage();
    }
}

$stmt = $pdo->prepare('SELECT `key`, value FROM settings WHERE `key` IN ("site_name", "default_locale", "theme")');
$stmt->execute();
$current = array_column($stmt->fetchAll(), 'value', 'key');

include __DIR__ . '/partials/header.php';
?>
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h4 mb-0">Ayarlar</h1>
  </div>
  <?php if ($message): ?>
    <div class="alert alert-success" role="alert"><?= h($message) ?></div>
  <?php elseif ($error): ?>
    <div class="alert alert-danger" role="alert"><?= h($error) ?></div>
  <?php endif; ?>
  <form method="post" class="card shadow-sm border-0">
    <div class="card-body">
      <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
      <div class="mb-3">
        <label class="form-label" for="site_name">Site adı</label>
        <input class="form-control" type="text" id="site_name" name="site_name" value="<?= h($current['site_name'] ?? 'SolveClone') ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label" for="default_locale">Varsayılan dil</label>
        <input class="form-control" type="text" id="default_locale" name="default_locale" value="<?= h($current['default_locale'] ?? 'tr') ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label" for="theme">Varsayılan tema</label>
        <select class="form-select" id="theme" name="theme">
          <option value="system" <?= ($current['theme'] ?? 'system') === 'system' ? 'selected' : '' ?>>Sistem</option>
          <option value="light" <?= ($current['theme'] ?? 'system') === 'light' ? 'selected' : '' ?>>Aydınlık</option>
          <option value="dark" <?= ($current['theme'] ?? 'system') === 'dark' ? 'selected' : '' ?>>Karanlık</option>
        </select>
      </div>
    </div>
    <div class="card-footer bg-white text-end">
      <button type="submit" class="btn btn-primary">Kaydet</button>
    </div>
  </form>
<?php include __DIR__ . '/partials/footer.php';
