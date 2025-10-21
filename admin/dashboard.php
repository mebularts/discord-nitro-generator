<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use App\Models\Riddle;

$pdo = db();
$stats = Riddle::stats();
$latestRiddles = $pdo->query('SELECT id, title, slug, published_at FROM riddles ORDER BY published_at DESC, id DESC LIMIT 6')->fetchAll();

admin_layout(
    __('admin.dashboard.title', 'Control center'),
    function () use ($stats, $latestRiddles) {
        ?>
        <div class="row g-4 mb-4">
          <div class="col-md-3">
            <div class="card shadow-sm">
              <div class="card-body">
                <div class="text-uppercase text-muted small"><?= __('admin.dashboard.total_riddles', 'Published riddles') ?></div>
                <div class="display-6 fw-semibold"><?= number_format((int) ($stats['riddles'] ?? 0)) ?></div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card shadow-sm">
              <div class="card-body">
                <div class="text-uppercase text-muted small"><?= __('admin.dashboard.total_users', 'Active users') ?></div>
                <div class="display-6 fw-semibold"><?= number_format((int) ($stats['users'] ?? 0)) ?></div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card shadow-sm">
              <div class="card-body">
                <div class="text-uppercase text-muted small"><?= __('admin.dashboard.total_votes', 'Votes cast') ?></div>
                <div class="display-6 fw-semibold"><?= number_format((int) ($stats['votes'] ?? 0)) ?></div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card shadow-sm">
              <div class="card-body">
                <div class="text-uppercase text-muted small"><?= __('admin.dashboard.total_views', 'Total views') ?></div>
                <div class="display-6 fw-semibold"><?= number_format((int) ($stats['views'] ?? 0)) ?></div>
              </div>
            </div>
          </div>
        </div>

        <div class="card shadow-sm">
          <div class="card-header bg-white">
            <h2 class="h5 mb-0"><?= __('admin.dashboard.latest_riddles', 'Latest submissions') ?></h2>
          </div>
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr>
                  <th scope="col">ID</th>
                  <th scope="col"><?= __('admin.forms.title', 'Title') ?></th>
                  <th scope="col">URL</th>
                  <th scope="col">Published</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($latestRiddles as $row): ?>
                  <tr>
                    <td><?= (int) $row['id'] ?></td>
                    <td><?= h($row['title']) ?></td>
                    <td><a href="/riddle/<?= h($row['slug']) ?>" target="_blank">/riddle/<?= h($row['slug']) ?></a></td>
                    <td><?= h($row['published_at']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php
    },
    [
        'active' => 'dashboard',
        'subtitle' => __('admin.dashboard.subtitle', 'Monitor growth, translation health and engagement at a glance.'),
    ]
);
