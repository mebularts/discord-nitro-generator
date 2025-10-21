<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$pdo = db();
$alerts = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'bulk-create') {
        $count = max(1, min(200, (int) ($_POST['count'] ?? 10)));
        $role = $_POST['role'] ?? 'editor';
        $prefix = preg_replace('/[^a-z0-9]/i', '', $_POST['prefix'] ?? 'tester');
        $password = $_POST['password'] ?? 'changeme';
        $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, name, role) VALUES (?, ?, ?, ?)');
        for ($i = 1; $i <= $count; $i++) {
            $email = strtolower($prefix . $i . '@example.com');
            $hash = password_hash($password . $i, PASSWORD_BCRYPT);
            try {
                $stmt->execute([$email, $hash, ucfirst($prefix) . ' ' . $i, $role]);
            } catch (\Throwable $e) {
                continue;
            }
        }
        $alerts[] = ['type' => 'success', 'message' => __('admin.users.bulk_create', 'Bulk create test users') . ' ✔'];
    }
}

$list = $pdo->query('SELECT id, email, name, role, is_active, created_at FROM users ORDER BY id DESC LIMIT 200')->fetchAll();

admin_layout(
    __('admin.users.title', 'Manage users'),
    function () use ($list) {
        ?>
        <div class="card shadow-sm mb-4">
          <div class="card-body">
            <form method="post" class="row g-3">
              <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
              <input type="hidden" name="action" value="bulk-create">
              <div class="col-md-3">
                <label class="form-label"><?= __('admin.forms.count', 'Count') ?></label>
                <input type="number" name="count" class="form-control" value="10" min="1" max="200">
              </div>
              <div class="col-md-3">
                <label class="form-label"><?= __('admin.forms.role', 'Role') ?></label>
                <select name="role" class="form-select">
                  <option value="editor">Editor</option>
                  <option value="moderator">Moderator</option>
                  <option value="admin">Admin</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label"><?= __('admin.forms.prefix', 'Prefix') ?></label>
                <input name="prefix" class="form-control" value="tester">
              </div>
              <div class="col-md-3">
                <label class="form-label">Password base</label>
                <input name="password" class="form-control" value="pass">
                <small class="text-muted">Actual password will be base + index (e.g. pass1)</small>
              </div>
              <div class="col-12 text-end">
                <button class="btn btn-primary"><?= __('admin.users.bulk_create', 'Bulk create test users') ?></button>
              </div>
            </form>
          </div>
        </div>

        <div class="card shadow-sm">
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Email</th>
                  <th><?= __('admin.forms.name', 'Name') ?></th>
                  <th><?= __('admin.forms.role', 'Role') ?></th>
                  <th>Status</th>
                  <th>Created</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($list as $user): ?>
                  <tr>
                    <td><?= (int) $user['id'] ?></td>
                    <td><?= h($user['email']) ?></td>
                    <td><?= h($user['name']) ?></td>
                    <td><span class="badge bg-secondary"><?= h($user['role']) ?></span></td>
                    <td><?= $user['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                    <td><?= h($user['created_at']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php
    },
    [
        'active' => 'users',
        'alerts' => $alerts,
    ]
);
