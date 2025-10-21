<?php
declare(strict_types=1);
?>
<div class="row g-4">
    <div class="col-md-4">
        <div class="card bg-dark border-0 shadow-lg">
            <div class="card-body">
                <h6 class="text-secondary text-uppercase">Riddles</h6>
                <p class="display-6 fw-bold text-info mb-0"><?= (int) $stats['riddles'] ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-dark border-0 shadow-lg">
            <div class="card-body">
                <h6 class="text-secondary text-uppercase">Users</h6>
                <p class="display-6 fw-bold text-success mb-0"><?= (int) $stats['users'] ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-dark border-0 shadow-lg">
            <div class="card-body">
                <h6 class="text-secondary text-uppercase">Pages</h6>
                <p class="display-6 fw-bold text-warning mb-0"><?= (int) $stats['pages'] ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card bg-dark border-0 shadow-lg mt-4">
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Latest riddles</h5>
        <a href="/admin/riddles.php" class="btn btn-sm btn-outline-info">Manage</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-dark table-hover mb-0">
            <thead>
            <tr>
                <th>Title</th>
                <th>Published</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($latestRiddles as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($item['published_at'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-light" href="/riddles/<?= urlencode($item['slug']) ?>" target="_blank">View</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
