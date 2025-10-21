<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$pdo = db();
$alerts = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $slug = trim($_POST['slug'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $body = $_POST['body'] ?? '';
    if ($slug && $title) {
        $stmt = $pdo->prepare('INSERT INTO pages (slug, title, body, updated_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE title = VALUES(title), body = VALUES(body), updated_at = NOW()');
        $stmt->execute([$slug, $title, $body]);
        $alerts[] = ['type' => 'success', 'message' => __('admin.forms.save', 'Save') . ' ✔'];
    }
}

$list = $pdo->query('SELECT slug, title, updated_at FROM pages ORDER BY slug ASC')->fetchAll();

admin_layout(
    __('admin.pages.title', 'Manage static pages'),
    function () use ($list) {
        ?>
        <div class="card shadow-sm mb-4">
          <div class="card-body">
            <form method="post" class="row g-3">
              <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
              <div class="col-md-4">
                <label class="form-label"><?= __('admin.forms.slug', 'Slug') ?></label>
                <input name="slug" class="form-control" placeholder="terms" required>
              </div>
              <div class="col-md-8">
                <label class="form-label"><?= __('admin.forms.title', 'Title') ?></label>
                <input name="title" class="form-control" required>
              </div>
              <div class="col-12">
                <label class="form-label"><?= __('admin.forms.body', 'Body') ?></label>
                <textarea name="body" class="form-control" rows="8"></textarea>
                <small class="text-muted"><?= __('admin.pages.editor_help', 'You can paste Markdown or HTML. The frontend will render safely.') ?></small>
              </div>
              <div class="col-12 text-end">
                <button class="btn btn-primary"><?= __('admin.forms.save', 'Save') ?></button>
              </div>
            </form>
          </div>
        </div>

        <div class="card shadow-sm">
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr>
                  <th>Slug</th>
                  <th><?= __('admin.forms.title', 'Title') ?></th>
                  <th>Updated</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($list as $page): ?>
                  <tr>
                    <td><?= h($page['slug']) ?></td>
                    <td><?= h($page['title']) ?></td>
                    <td><?= h($page['updated_at']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php
    },
    [
        'active' => 'pages',
        'alerts' => $alerts,
    ]
);
