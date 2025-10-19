<?php
require_once __DIR__.'/bootstrap.php';
must_login(['users']);

$permissions = permission_labels();

if (is_post()) {
  csrf_check();
  $action = $_POST['action'] ?? '';
  if ($action === 'add') {
    $name = trim($_POST['name'] ?? '');
    $perms = $_POST['permissions'] ?? [];
    if ($name === '') {
      set_flash('error', 'Rol adı gerekli.');
    } else {
      q($pdo, 'INSERT INTO admin_roles (name, permissions) VALUES (?,?)', [$name, json_encode(array_values(array_intersect(array_keys($permissions), $perms)))]);
      set_flash('success', 'Rol eklendi.');
    }
    redirect('/admin/roles.php');
  } elseif ($action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $perms = $_POST['permissions'] ?? [];
    q($pdo, 'UPDATE admin_roles SET permissions=? WHERE id=?', [json_encode(array_values(array_intersect(array_keys($permissions), $perms))), $id]);
    set_flash('success', 'Rol güncellendi.');
    redirect('/admin/roles.php');
  } elseif ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $count = (int)q($pdo, 'SELECT COUNT(*) FROM admin_users WHERE role_id=?', [$id])->fetchColumn();
    if ($count > 0) {
      set_flash('error', 'Bu role atanmış kullanıcılar var.');
    } else {
      q($pdo, 'DELETE FROM admin_roles WHERE id=?', [$id]);
      set_flash('success', 'Rol silindi.');
    }
    redirect('/admin/roles.php');
  }
}

$roles = q($pdo, 'SELECT * FROM admin_roles ORDER BY name')->fetchAll();

admin_render_header('Roller', 'roles');
?>
<div class="grid lg:grid-cols-2 gap-6">
  <section class="border rounded-xl bg-white p-5">
    <h2 class="text-lg font-semibold text-slate-700 mb-3">Yeni Rol</h2>
    <form method="post" class="space-y-4">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add">
      <label class="text-sm text-slate-600">Rol Adı
        <input type="text" name="name" class="mt-1 border rounded-lg w-full p-2" required>
      </label>
      <div>
        <div class="text-sm text-slate-600 mb-2">Yetkiler</div>
        <div class="grid md:grid-cols-2 gap-2">
          <?php foreach ($permissions as $key => $label): ?>
            <label class="flex items-center gap-2 text-sm">
              <input type="checkbox" name="permissions[]" value="<?= h($key) ?>"> <?= h($label) ?>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
      <button class="px-4 py-2 rounded-lg bg-slate-900 text-white">Kaydet</button>
    </form>
  </section>

  <section class="border rounded-xl bg-white p-5">
    <h2 class="text-lg font-semibold text-slate-700 mb-3">Mevcut Roller</h2>
    <div class="space-y-4">
      <?php foreach ($roles as $role): $rolePerms = json_decode($role['permissions'] ?? '[]', true) ?: []; ?>
        <form method="post" class="border rounded-lg p-4 space-y-3">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$role['id'] ?>">
          <div class="font-semibold text-slate-700"><?= h($role['name']) ?></div>
          <div class="grid md:grid-cols-2 gap-2 text-sm">
            <?php foreach ($permissions as $key => $label): ?>
              <label class="flex items-center gap-2">
                <input type="checkbox" name="permissions[]" value="<?= h($key) ?>" <?= in_array($key, $rolePerms, true) ? 'checked' : '' ?>> <?= h($label) ?>
              </label>
            <?php endforeach; ?>
          </div>
          <div class="flex items-center gap-2">
            <button name="action" value="update" class="px-3 py-2 rounded-lg bg-slate-900 text-white text-sm">Güncelle</button>
            <button name="action" value="delete" class="px-3 py-2 rounded-lg bg-rose-500 text-white text-sm" onclick="return confirm('Silmek istediğinize emin misiniz?');">Sil</button>
          </div>
        </form>
      <?php endforeach; if (empty($roles)): ?>
        <div class="text-slate-500">Rol bulunamadı.</div>
      <?php endif; ?>
    </div>
  </section>
</div>
<?php
admin_render_footer();
