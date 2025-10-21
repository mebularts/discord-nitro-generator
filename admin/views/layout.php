<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Admin • ' . $appName, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background: #020617; color: #e2e8f0; font-family: 'Inter', system-ui, sans-serif; }
        .sidebar { width: 240px; }
        .sidebar a { color: inherit; text-decoration: none; display: block; padding: .75rem 1rem; border-radius: .75rem; }
        .sidebar a.active, .sidebar a:hover { background: rgba(14,165,233,.15); color: #38bdf8; }
        main { flex: 1; }
    </style>
</head>
<body>
<div class="d-flex min-vh-100">
    <nav class="sidebar bg-dark-subtle p-3 d-none d-lg-block">
        <h5 class="text-light fw-semibold">SolveClone Admin</h5>
        <div class="mt-4 d-flex flex-column gap-1">
            <a href="/admin/dashboard.php" class="<?= ($active ?? '') === 'dashboard' ? 'active' : '' ?>"><i class="bi bi-speedometer"></i> Dashboard</a>
            <a href="/admin/riddles.php" class="<?= ($active ?? '') === 'riddles' ? 'active' : '' ?>"><i class="bi bi-lightbulb"></i> Riddles</a>
            <a href="/admin/pages.php" class="<?= ($active ?? '') === 'pages' ? 'active' : '' ?>"><i class="bi bi-file-text"></i> Pages</a>
            <a href="/admin/users.php" class="<?= ($active ?? '') === 'users' ? 'active' : '' ?>"><i class="bi bi-people"></i> Users</a>
            <a href="/admin/settings.php" class="<?= ($active ?? '') === 'settings' ? 'active' : '' ?>"><i class="bi bi-gear"></i> Settings</a>
        </div>
    </nav>
    <main class="p-4 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 mb-1"><?= htmlspecialchars($title ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></h1>
                <p class="text-secondary mb-0">Signed in as <?= htmlspecialchars($admin['name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <div class="d-flex gap-2">
                <a href="/" class="btn btn-outline-info btn-sm">View site</a>
                <a href="/admin/logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
            </div>
        </div>
        <?= $content ?? '' ?>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
