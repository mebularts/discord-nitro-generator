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
                <h5 class="mb-0">Pages</h5>
                <a href="/admin/pages.php" class="btn btn-sm btn-outline-light">New</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-dark table-hover mb-0">
                    <thead>
                    <tr>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Locale</th>
                        <th>Updated</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($pages as $page): ?>
                        <tr>
                            <td><?= htmlspecialchars($page['title'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($page['slug'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= strtoupper($page['locale']) ?></td>
                            <td><?= htmlspecialchars($page['updated_at'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="/admin/pages.php?id=<?= (int) $page['id'] ?>" class="btn btn-outline-info">Edit</a>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Delete this page?');">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="delete" value="<?= (int) $page['id'] ?>">
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
                <h5 class="mb-0"><?= $editing ? 'Edit page' : 'New page' ?></h5>
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
                        <label class="form-label">Locale</label>
                        <select name="locale" class="form-select">
                            <?php foreach (available_locales() as $locale): ?>
                                <option value="<?= $locale ?>" <?= (($editing['locale'] ?? APP_LOCALE) === $locale) ? 'selected' : '' ?>><?= strtoupper($locale) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Body</label>
                        <textarea name="body" class="form-control" rows="6"><?= htmlspecialchars($editing['body'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
