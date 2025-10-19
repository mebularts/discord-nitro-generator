<?php
require_once __DIR__.'/bootstrap.php';
must_login(['users']);

$roles = q($pdo, 'SELECT id, name FROM admin_roles ORDER BY name')->fetchAll();
$current = current_admin();
$isSuper = !empty($current['is_super']);

if (is_post()) {
  csrf_check();
  $action = $_POST['action'] ?? '';
  if ($action === 'add_user') {
    $username = trim($_POST['username'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $roleId   = (int)($_POST['role_id'] ?? 0) ?: null;
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $isSuperNew = $isSuper && isset($_POST['is_super']) ? 1 : 0;
    if ($username === '' || $password === '') {
      set_flash('error', 'Kullanıcı adı ve şifre gereklidir.');
    } else {
      $exists = q($pdo, 'SELECT COUNT(*) FROM admin_users WHERE username=?', [$username])->fetchColumn();
      if ($exists) {
        set_flash('error', 'Bu kullanıcı adı zaten kullanılıyor.');
      } else {
        q($pdo, 'INSERT INTO admin_users (username, full_name, password, role_id, is_active, is_super) VALUES (?,?,?,?,?,?)', [$username, $fullName, password_hash($password, PASSWORD_DEFAULT), $isSuperNew ? null : $roleId, $isActive, $isSuperNew]);
        set_flash('success', 'Kullanıcı eklendi.');
      }
    }
    redirect('/admin/users.php');
  } elseif ($action === 'update_user') {
    $id = (int)($_POST['id'] ?? 0);
    $fullName = trim($_POST['full_name'] ?? '');
    $roleId = (int)($_POST['role_id'] ?? 0) ?: null;
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $isSuperUpdate = $isSuper && isset($_POST['is_super']) ? 1 : 0;
    q($pdo, 'UPDATE admin_users SET full_name=?, role_id=?, is_active=?, is_super=? WHERE id=?', [$fullName, $isSuperUpdate ? null : $roleId, $isActive, $isSuperUpdate, $id]);
    set_flash('success', 'Kullanıcı güncellendi.');
    redirect('/admin/users.php');
  } elseif ($action === 'reset_password') {
    $id = (int)($_POST['id'] ?? 0);
    $password = $_POST['password'] ?? '';
    if ($password === '') {
      set_flash('error', 'Şifre boş olamaz.');
    } else {
      q($pdo, 'UPDATE admin_users SET password=? WHERE id=?', [password_hash($password, PASSWORD_DEFAULT), $id]);
      set_flash('success', 'Şifre güncellendi.');
    }
    redirect('/admin/users.php');
  }
}

$users = q($pdo, 'SELECT u.*, r.name role_name FROM admin_users u LEFT JOIN admin_roles r ON r.id=u.role_id ORDER BY u.created_at DESC')->fetchAll();

admin_render_header('Kullanıcı Yönetimi', 'users');
?>
<section class="border rounded-xl bg-white p-5 mb-6">
  <h2 class="text-lg font-semibold text-slate-700 mb-4">Yeni Kullanıcı</h2>
  <form method="post" class="grid md:grid-cols-3 gap-4">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add_user">
    <label class="text-sm text-slate-600">Kullanıcı Adı
      <input type="text" name="username" class="mt-1 border rounded-lg w-full p-2" required>
    </label>
    <label class="text-sm text-slate-600">Ad Soyad
      <input type="text" name="full_name" class="mt-1 border rounded-lg w-full p-2">
    </label>
    <label class="text-sm text-slate-600">Şifre
      <input type="password" name="password" class="mt-1 border rounded-lg w-full p-2" required>
    </label>
    <label class="text-sm text-slate-600">Rol
      <select name="role_id" class="mt-1 border rounded-lg w-full p-2">
        <option value="0">Seçiniz</option>
        <?php foreach ($roles as $role): ?>
          <option value="<?= (int)$role['id'] ?>"><?= h($role['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label class="text-sm text-slate-600">Durum
      <input type="checkbox" name="is_active" value="1" checked> Aktif
    </label>
    <?php if ($isSuper): ?>
      <label class="text-sm text-slate-600">Süper Yönetici
        <input type="checkbox" name="is_super" value="1"> Tüm yetkiler
      </label>
    <?php endif; ?>
    <div class="md:col-span-3">
      <button class="px-4 py-2 rounded-lg bg-slate-900 text-white">Kaydet</button>
    </div>
  </form>
</section>

<section class="border rounded-xl bg-white p-5">
  <h2 class="text-lg font-semibold text-slate-700 mb-4">Kullanıcılar</h2>
  <div class="space-y-4">
    <?php foreach ($users as $user): ?>
      <div class="border rounded-lg p-4">
        <form method="post" class="grid md:grid-cols-4 gap-4 items-end">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="update_user">
          <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
          <div>
            <div class="text-xs text-slate-500">Kullanıcı</div>
            <div class="font-semibold text-slate-700"><?= h($user['username']) ?></div>
          </div>
          <label class="text-sm text-slate-600">Ad Soyad
            <input type="text" name="full_name" value="<?= h($user['full_name'] ?? '') ?>" class="mt-1 border rounded-lg w-full p-2">
          </label>
          <label class="text-sm text-slate-600">Rol
            <select name="role_id" class="mt-1 border rounded-lg w-full p-2">
              <option value="0">Seçiniz</option>
              <?php foreach ($roles as $role): ?>
                <option value="<?= (int)$role['id'] ?>" <?= ($user['role_id'] ?? null) == $role['id'] ? 'selected' : '' ?>><?= h($role['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="text-sm text-slate-600">Durum
            <input type="checkbox" name="is_active" value="1" <?= !empty($user['is_active']) ? 'checked' : '' ?>> Aktif
          </label>
          <?php if ($isSuper): ?>
            <label class="text-sm text-slate-600">Süper Yönetici
              <input type="checkbox" name="is_super" value="1" <?= !empty($user['is_super']) ? 'checked' : '' ?>>
            </label>
          <?php endif; ?>
          <div class="md:col-span-4 flex items-center gap-2">
            <button class="px-3 py-2 rounded-lg bg-slate-900 text-white text-sm">Güncelle</button>
            <button formaction="" formmethod="post" name="action" value="reset_password" class="px-3 py-2 rounded-lg bg-amber-500 text-white text-sm" onclick="return confirm('Şifreyi sıfırlamak istiyor musunuz?');">Şifre Sıfırla</button>
            <input type="password" name="password" placeholder="Yeni şifre" class="border rounded-lg p-2 text-sm">
          </div>
        </form>
      </div>
    <?php endforeach; if (empty($users)): ?>
      <div class="text-slate-500">Kullanıcı bulunamadı.</div>
    <?php endif; ?>
  </div>
</section>
<?php
admin_render_footer();
