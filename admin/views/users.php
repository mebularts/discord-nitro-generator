<?php
declare(strict_types=1);
?>
<?php if (!empty($flash)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card bg-dark border-0 shadow-lg">
            <div class="card-header bg-transparent border-0">
                <h5 class="mb-0">Users</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-dark table-hover mb-0">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(ucfirst($user['role']), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $user['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                            <td class="text-end">
                                <form method="post" class="d-inline">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="toggle" value="<?= (int) $user['id'] ?>">
                                    <input type="hidden" name="active" value="<?= $user['is_active'] ? 0 : 1 ?>">
                                    <button class="btn btn-sm btn-outline-light" type="submit">Toggle</button>
                                </form>
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
                <h5 class="mb-0">Invite new user</h5>
            </div>
            <div class="card-body">
                <form method="post" class="d-grid gap-3">
                    <?= csrf_input() ?>
                    <div>
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="admin">Admin</option>
                            <option value="editor">Editor</option>
                            <option value="moderator">Moderator</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Create user</button>
                </form>
            </div>
        </div>
    </div>
</div>
