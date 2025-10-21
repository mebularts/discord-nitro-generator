<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use App\I18n\Translator;

$pdo = db();
$alerts = [];
$locales = locales_enabled();
$defaultLocale = resolve_locale();
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
        $body = trim($_POST['body'] ?? '');
        $answer = trim($_POST['answer'] ?? '');
        $difficulty = $_POST['difficulty'] ?? 'easy';
        $length = $_POST['length'] ?? 'short';
        $status = $_POST['status'] ?? 'draft';
        $categoryIds = array_map('intval', $_POST['category_ids'] ?? []);
        $tags = array_filter(array_map('trim', explode(',', $_POST['tags'] ?? '')));

        $stmt = $pdo->prepare('INSERT INTO riddles (slug, title, body, answer, difficulty, length, status, published_at) VALUES (?,?,?,?,?,?,?,IF(?="published", NOW(), NULL))');
        $stmt->execute([$slug, $title, $body, $answer, $difficulty, $length, $status, $status]);
        $riddleId = (int) $pdo->lastInsertId();

        if ($categoryIds) {
            $insertCategory = $pdo->prepare('INSERT INTO riddle_category (riddle_id, category_id) VALUES (?, ?)');
            foreach ($categoryIds as $categoryId) {
                $insertCategory->execute([$riddleId, $categoryId]);
            }
        }

        if ($tags) {
            $insertTag = $pdo->prepare('INSERT INTO tags (slug, name) VALUES (?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name)');
            $attachTag = $pdo->prepare('INSERT IGNORE INTO riddle_tag (riddle_id, tag_id) VALUES (?, ?)');
            foreach ($tags as $tag) {
                $tagSlug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $tag));
                $insertTag->execute([$tagSlug, $tag]);
                $tagId = (int) $pdo->lastInsertId();
                if (!$tagId) {
                    $fetch = $pdo->prepare('SELECT id FROM tags WHERE slug = ?');
                    $fetch->execute([$tagSlug]);
                    $tagId = (int) $fetch->fetchColumn();
                }
                if ($tagId) {
                    $attachTag->execute([$riddleId, $tagId]);
                }
            }
        }

        $alerts[] = ['type' => 'success', 'message' => __('admin.riddles.create', 'Create new riddle') . ' ✔'];
    }

    if ($action === 'bulk_status') {
        $ids = array_map('intval', $_POST['ids'] ?? []);
        $newStatus = $_POST['status'] ?? 'draft';
        if ($ids) {
            $in = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("UPDATE riddles SET status = ?, published_at = CASE WHEN ?='published' THEN NOW() ELSE NULL END WHERE id IN ($in)");
            $stmt->execute(array_merge([$newStatus, $newStatus], $ids));
            $alerts[] = ['type' => 'success', 'message' => __('admin.forms.status', 'Status') . ' updated.'];
        }
    }

    if ($action === 'translate') {
        $ids = array_map('intval', $_POST['ids'] ?? []);
        if ($ids) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("SELECT id, title, body, answer FROM riddles WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $rows = $stmt->fetchAll();
            foreach ($rows as $row) {
                foreach ($locales as $locale) {
                    if ($locale === $defaultLocale) {
                        continue;
                    }
                    Translator::translate($row['title'], strtoupper($locale), strtoupper($defaultLocale));
                    Translator::translate($row['body'], strtoupper($locale), strtoupper($defaultLocale));
                    Translator::translate($row['answer'], strtoupper($locale), strtoupper($defaultLocale));
                }
            }
            $alerts[] = ['type' => 'info', 'message' => __('admin.riddles.generate_translations', 'Generate translations') . ' ✔'];
        }
    }
}

$list = $pdo->query('SELECT id, slug, title, status, difficulty, length, up_votes, down_votes FROM riddles ORDER BY id DESC LIMIT 200')->fetchAll();

admin_layout(
    __('admin.riddles.title', 'Manage riddles'),
    function () use ($alerts, $categories, $list, $locales) {
        ?>
        <div class="card shadow-sm mb-4">
          <div class="card-body">
            <form method="post" class="row g-3">
              <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
              <input type="hidden" name="action" value="create">
              <div class="col-md-6">
                <label class="form-label"><?= __('admin.forms.title', 'Title') ?></label>
                <input name="title" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label"><?= __('admin.forms.slug', 'Slug') ?></label>
                <input name="slug" class="form-control" placeholder="auto-generated if empty">
              </div>
              <div class="col-md-6">
                <label class="form-label"><?= __('admin.forms.difficulty', 'Difficulty') ?></label>
                <select name="difficulty" class="form-select">
                  <option value="easy">Easy</option>
                  <option value="medium">Medium</option>
                  <option value="hard">Hard</option>
                  <option value="difficult">Difficult</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label"><?= __('admin.forms.length', 'Length') ?></label>
                <select name="length" class="form-select">
                  <option value="short">Short</option>
                  <option value="long">Long</option>
                  <option value="simple">Simple</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label"><?= __('admin.forms.status', 'Status') ?></label>
                <select name="status" class="form-select">
                  <option value="draft">Draft</option>
                  <option value="published">Published</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label"><?= __('admin.forms.category', 'Category') ?></label>
                <select name="category_ids[]" class="form-select" multiple>
                  <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>"><?= h($category['name']) ?></option>
                  <?php endforeach; ?>
                </select>
                <small class="text-muted">Ctrl + click to select multiple</small>
              </div>
              <div class="col-12">
                <label class="form-label"><?= __('admin.forms.body', 'Body') ?></label>
                <textarea name="body" class="form-control" rows="4" required></textarea>
              </div>
              <div class="col-12">
                <label class="form-label"><?= __('admin.forms.answer', 'Answer') ?></label>
                <textarea name="answer" class="form-control" rows="3" required></textarea>
              </div>
              <div class="col-12">
                <label class="form-label"><?= __('admin.forms.tags', 'Tags (comma separated)') ?></label>
                <input name="tags" class="form-control" placeholder="logic, math, wordplay">
              </div>
              <div class="col-12 text-end">
                <button class="btn btn-primary">
                  <?= __('admin.forms.save', 'Save') ?>
                </button>
              </div>
            </form>
          </div>
        </div>

        <form method="post" class="card shadow-sm">
          <div class="card-header bg-white d-flex flex-column flex-md-row gap-2 align-items-md-center justify-content-between">
            <div>
              <h2 class="h5 mb-0"><?= __('admin.riddles.subtitle', 'Filter, publish and translate riddles in every language.') ?></h2>
            </div>
            <div class="d-flex gap-2">
              <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
              <select name="status" class="form-select form-select-sm">
                <option value="published"><?= __('admin.forms.publish', 'Publish') ?></option>
                <option value="draft"><?= __('admin.forms.unpublish', 'Unpublish') ?></option>
              </select>
              <button class="btn btn-sm btn-outline-primary" name="action" value="bulk_status"><?= __('admin.forms.actions', 'Actions') ?></button>
              <button class="btn btn-sm btn-outline-secondary" name="action" value="translate"><?= __('admin.riddles.generate_translations', 'Generate translations') ?></button>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr>
                  <th scope="col"><input type="checkbox" data-action="select-all"></th>
                  <th scope="col">ID</th>
                  <th scope="col"><?= __('admin.forms.title', 'Title') ?></th>
                  <th scope="col">Slug</th>
                  <th scope="col"><?= __('admin.forms.difficulty', 'Difficulty') ?></th>
                  <th scope="col"><?= __('admin.forms.length', 'Length') ?></th>
                  <th scope="col"><?= __('admin.forms.status', 'Status') ?></th>
                  <th scope="col"><?= __('riddle.rating', 'Approval') ?></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($list as $row): ?>
                  <?php $totalVotes = max(1, (int) $row['up_votes'] + (int) $row['down_votes']); ?>
                  <tr>
                    <td><input type="checkbox" name="ids[]" value="<?= (int) $row['id'] ?>"></td>
                    <td><?= (int) $row['id'] ?></td>
                    <td><?= h($row['title']) ?></td>
                    <td><a href="/riddle/<?= h($row['slug']) ?>" target="_blank"><?= h($row['slug']) ?></a></td>
                    <td><?= h($row['difficulty']) ?></td>
                    <td><?= h($row['length']) ?></td>
                    <td><span class="badge bg-<?= $row['status'] === 'published' ? 'success' : 'secondary' ?>"><?= h($row['status']) ?></span></td>
                    <td><?= number_format(((int) $row['up_votes'] / $totalVotes) * 100, 1) ?>%</td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </form>
        <script>
        document.querySelector('[data-action="select-all"]').addEventListener('change', (e) => {
          document.querySelectorAll('input[name="ids[]"]').forEach(cb => cb.checked = e.target.checked);
        });
        </script>
        <?php
    },
    [
        'active' => 'riddles',
        'subtitle' => __('admin.riddles.subtitle', 'Filter, publish and translate riddles in every language.'),
        'alerts' => $alerts,
    ]
);
