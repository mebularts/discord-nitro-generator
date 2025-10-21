<?php
declare(strict_types=1);
?>
<?php if (!empty($flash)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card bg-dark border-0 shadow-lg">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Riddles</h5>
                <a href="/admin/riddles.php" class="btn btn-sm btn-outline-light">New</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-dark table-hover mb-0">
                    <thead>
                    <tr>
                        <th>Title</th>
                        <th>Difficulty</th>
                        <th>Status</th>
                        <th>Votes</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($riddles as $riddle): ?>
                        <tr>
                            <td><?= htmlspecialchars($riddle['title'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(ucfirst($riddle['difficulty']), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $riddle['is_published'] ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Draft</span>' ?></td>
                            <td><?= (int) $riddle['votes'] ?></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="/admin/riddles.php?id=<?= (int) $riddle['id'] ?>" class="btn btn-outline-info">Edit</a>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Delete this riddle?');">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="delete" value="<?= (int) $riddle['id'] ?>">
                                        <button class="btn btn-outline-danger" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card bg-dark border-0 shadow-lg">
            <div class="card-header bg-transparent border-0">
                <h5 class="mb-0"><?= $editing ? 'Edit riddle' : 'New riddle' ?></h5>
            </div>
            <div class="card-body">
                <form method="post" class="d-grid gap-3">
                    <?= csrf_input() ?>
                    <?php if ($editing): ?>
                        <input type="hidden" name="id" value="<?= (int) $editing['id'] ?>">
                    <?php endif; ?>
                    <div>
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($editing['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($editing['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label class="form-label">Body</label>
                        <textarea name="body" class="form-control" rows="4" required><?= htmlspecialchars($editing['body'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div>
                        <label class="form-label">Answer</label>
                        <textarea name="answer" class="form-control" rows="3" required><?= htmlspecialchars($editing['answer'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div>
                        <label class="form-label">Difficulty</label>
                        <select name="difficulty" class="form-select">
                            <?php foreach (['easy', 'medium', 'hard'] as $option): ?>
                                <option value="<?= $option ?>" <?= (($editing['difficulty'] ?? 'medium') === $option) ? 'selected' : '' ?>><?= ucfirst($option) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Locale</label>
                        <select name="locale" class="form-select">
                            <?php foreach (available_locales() as $locale): ?>
                                <option value="<?= $locale ?>" <?= (($editing['locale'] ?? APP_LOCALE) === $locale) ? 'selected' : '' ?>><?= strtoupper($locale) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Categories</label>
                        <select name="categories[]" class="form-select" multiple>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int) $category['id'] ?>" <?= in_array((int) $category['id'], $selectedCategories, true) ? 'selected' : '' ?>><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-secondary d-block">Hold Ctrl or Cmd to select multiple.</small>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_published" <?= ($editing['is_published'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label">Published</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
