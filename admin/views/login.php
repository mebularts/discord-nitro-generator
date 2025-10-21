<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login • <?= htmlspecialchars($appName ?? 'SolveClone', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { min-height: 100vh; background: radial-gradient(circle at top, #1d4ed8, #020617); display: flex; align-items: center; justify-content: center; font-family: 'Inter', system-ui, sans-serif; }
        .card { background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(148, 163, 184, 0.3); border-radius: 1.5rem; padding: 2.5rem; width: 360px; }
    </style>
</head>
<body>
<div class="card shadow-lg">
    <div class="text-center mb-4">
        <h1 class="h4 text-white mb-1">SolveClone Admin</h1>
        <p class="text-secondary mb-0">Sign in to manage content</p>
    </div>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <form method="post" class="d-grid gap-3">
        <?= csrf_input() ?>
        <div>
            <label class="form-label text-secondary">Email</label>
            <input type="email" class="form-control" name="email" required value="<?= htmlspecialchars(old('email', ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div>
            <label class="form-label text-secondary">Password</label>
            <input type="password" class="form-control" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Sign in</button>
    </form>
</div>
</body>
</html>
