<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use App\Models\User;
use function App\csrf_check;
use function App\csrf_token;
use function App\current_user;
use function App\h;
use function App\redirect;

$user = current_user();
if (!$user) {
    redirect('/login.php');
}

$pageTitle = 'Profil Ayarları';
$message = null;
$error = null;
$social = $user['social_links'] ? json_decode($user['social_links'], true, 512, JSON_THROW_ON_ERROR) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrf_check();
        $action = $_POST['action'] ?? 'update';

        if ($action === 'update') {
            $data = [
                'bio' => $_POST['bio'] ?? '',
                'profile_color' => $_POST['profile_color'] ?? '',
                'questions_public' => isset($_POST['questions_public']),
                'social_links' => array_filter([
                    'instagram' => $_POST['instagram'] ?? '',
                    'twitter' => $_POST['twitter'] ?? '',
                    'youtube' => $_POST['youtube'] ?? '',
                    'tiktok' => $_POST['tiktok'] ?? '',
                    'github' => $_POST['github'] ?? '',
                    'linkedin' => $_POST['linkedin'] ?? '',
                ]),
            ];

            if (!empty($_POST['remove_avatar'])) {
                if (!empty($user['avatar'])) {
                    $existingPath = __DIR__ . $user['avatar'];
                    if (is_file($existingPath) && !unlink($existingPath)) {
                        throw new \RuntimeException('Önceki avatar silinemedi.');
                    }
                }
                $data['avatar'] = null;
            } elseif (!empty($_FILES['avatar']['tmp_name'])) {
                $file = $_FILES['avatar'];
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    throw new \RuntimeException('Avatar yüklenemedi.');
                }
                if ($file['size'] > 2 * 1024 * 1024) {
                    throw new \RuntimeException('Avatar en fazla 2MB olabilir.');
                }
                $allowed = ['image/jpeg' => '.jpg', 'image/png' => '.png', 'image/webp' => '.webp'];
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($file['tmp_name']);
                if (!isset($allowed[$mime])) {
                    throw new \RuntimeException('Desteklenmeyen dosya türü.');
                }

                $avatarDirectory = __DIR__ . '/profiles/avatars';
                if (!is_dir($avatarDirectory)) {
                    if (!mkdir($avatarDirectory, 0755, true) && !is_dir($avatarDirectory)) {
                        throw new \RuntimeException('Avatar klasörü oluşturulamadı.');
                    }
                }

                $filename = bin2hex(random_bytes(16)) . $allowed[$mime];
                $target = $avatarDirectory . '/' . $filename;
                if (!move_uploaded_file($file['tmp_name'], $target)) {
                    throw new \RuntimeException('Dosya kaydedilemedi.');
                }

                if (!empty($user['avatar'])) {
                    $existingPath = __DIR__ . $user['avatar'];
                    if (is_file($existingPath) && !unlink($existingPath)) {
                        throw new \RuntimeException('Önceki avatar silinemedi.');
                    }
                }

                $data['avatar'] = '/profiles/avatars/' . $filename;
            } else {
                $data['avatar'] = $user['avatar'];
            }

            User::updateProfile((int) $user['id'], $data);
            $message = 'Profil güncellendi.';
        } elseif ($action === 'change_username') {
            if (!User::canChangeUsername($user)) {
                $next = User::usernameCooldown($user);
                $diff = $next ? $next->diff(new \DateTimeImmutable('now')) : null;
                $remaining = $diff ? $diff->format('%a gün %h saat') : 'bir süre';
                throw new \RuntimeException('Kullanıcı adı ' . $remaining . ' sonra değiştirilebilir.');
            }
            $newUsername = $_POST['new_username'] ?? '';
            User::changeUsername((int) $user['id'], $newUsername);
            $message = 'Kullanıcı adınız güncellendi.';
        }

        $user = User::find((int) $user['id']);
        $social = $user['social_links'] ? json_decode($user['social_links'], true, 512, JSON_THROW_ON_ERROR) : [];
    } catch (\Throwable $e) {
        error_log('[settings-profile] ' . $e->getMessage());
        $error = $e->getMessage();
    }
}

$cooldown = User::usernameCooldown($user ?? []);

require __DIR__ . '/partials/header.php';
?>
  <?php if ($message): ?>
    <div class="alert alert-success" role="alert"><?= h($message) ?></div>
  <?php elseif ($error): ?>
    <div class="alert alert-danger" role="alert"><?= h($error) ?></div>
  <?php endif; ?>

  <div class="row g-4">
    <div class="col-lg-7">
      <section class="card border-0 shadow-sm">
        <div class="card-body">
          <h2 class="h5 fw-semibold mb-3">Profil Detayları</h2>
          <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="update">
            <div class="mb-3">
              <label class="form-label" for="bio">Biyografi</label>
              <textarea class="form-control" id="bio" name="bio" rows="4" maxlength="500"><?= h($user['bio'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label" for="profile_color">Profil rengi</label>
              <input type="color" class="form-control form-control-color" id="profile_color" name="profile_color" value="<?= h($user['profile_color'] ?? '#2563eb') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label" for="avatar">Avatar</label>
              <input type="file" class="form-control" id="avatar" name="avatar" accept="image/png,image/jpeg,image/webp">
              <?php if (!empty($user['avatar'])): ?>
                <div class="form-check mt-2">
                  <input class="form-check-input" type="checkbox" id="remove_avatar" name="remove_avatar" value="1">
                  <label class="form-check-label" for="remove_avatar">Avatarı kaldır</label>
                </div>
              <?php endif; ?>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label" for="instagram">Instagram</label>
                <input class="form-control" type="url" id="instagram" name="instagram" value="<?= h($social['instagram'] ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="twitter">Twitter/X</label>
                <input class="form-control" type="url" id="twitter" name="twitter" value="<?= h($social['twitter'] ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="youtube">YouTube</label>
                <input class="form-control" type="url" id="youtube" name="youtube" value="<?= h($social['youtube'] ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="tiktok">TikTok</label>
                <input class="form-control" type="url" id="tiktok" name="tiktok" value="<?= h($social['tiktok'] ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="github">GitHub</label>
                <input class="form-control" type="url" id="github" name="github" value="<?= h($social['github'] ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="linkedin">LinkedIn</label>
                <input class="form-control" type="url" id="linkedin" name="linkedin" value="<?= h($social['linkedin'] ?? '') ?>">
              </div>
            </div>
            <div class="form-check form-switch mt-4">
              <input class="form-check-input" type="checkbox" id="questions_public" name="questions_public" <?= ($user['questions_public'] ?? 1) ? 'checked' : '' ?>>
              <label class="form-check-label" for="questions_public">Cevaplarım ana sayfada görünsün</label>
            </div>
            <button type="submit" class="btn btn-primary mt-4">Kaydet</button>
          </form>
        </div>
      </section>
    </div>
    <div class="col-lg-5">
      <section class="card border-0 shadow-sm">
        <div class="card-body">
          <h2 class="h5 fw-semibold mb-3">Kullanıcı adı</h2>
          <p class="text-muted small">Kullanıcı adınızı 40 günde bir değiştirebilirsiniz.</p>
          <form method="post" class="d-grid gap-3">
            <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="change_username">
            <div>
              <label class="form-label" for="new_username">Yeni kullanıcı adı</label>
              <input type="text" class="form-control" id="new_username" name="new_username" required>
            </div>
            <?php if ($cooldown && $cooldown > new \DateTimeImmutable('now')): ?>
              <div class="alert alert-warning mb-0" role="alert">
                Kullanıcı adınızı yeniden değiştirmek için <?= h($cooldown->format('d.m.Y H:i')) ?> tarihini beklemelisiniz.
              </div>
              <button type="submit" class="btn btn-secondary" disabled>Şu an değiştirilemez</button>
            <?php else: ?>
              <button type="submit" class="btn btn-outline-primary">Kullanıcı adını güncelle</button>
            <?php endif; ?>
          </form>
        </div>
      </section>
    </div>
  </div>
<?php require __DIR__ . '/partials/footer.php';
