<?php
require_once __DIR__.'/bootstrap.php';
must_login(['customers']);

$providers = q($pdo, 'SELECT id, name FROM providers ORDER BY name')->fetchAll();
$statusOptions = appointment_statuses();

$providerFilter = (int)($_GET['provider'] ?? 0);
$search = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$categoryFilter = $_GET['category'] ?? '';

$where = [];
$params = [];
if ($providerFilter > 0) {
  $where[] = 'a.provider_id = ?';
  $params[] = $providerFilter;
}
if ($search !== '') {
  $like = '%'.$search.'%';
  $where[] = '(a.full_name LIKE ? OR a.phone LIKE ? OR a.email LIKE ?)';
  array_push($params, $like, $like, $like);
}

$sql = 'SELECT COALESCE(NULLIF(a.email, ""), a.phone) AS customer_key,
               MAX(a.full_name) AS full_name,
               MAX(a.phone) AS phone,
               MAX(a.email) AS email,
               COUNT(*) AS total_count,
               SUM(a.status = "completed") AS completed_count,
               MIN(a.app_date) AS first_date,
               MAX(a.app_date) AS last_date,
               MAX(a.category) AS category,
               GROUP_CONCAT(DISTINCT a.status) AS statuses,
               GROUP_CONCAT(DISTINCT p.name ORDER BY p.name SEPARATOR ", ") AS providers
        FROM appointments a
        JOIN providers p ON p.id = a.provider_id';
if ($where) {
  $sql .= ' WHERE '.implode(' AND ', $where);
}
$sql .= ' GROUP BY customer_key HAVING customer_key IS NOT NULL ORDER BY last_date DESC LIMIT 200';

$rows = q($pdo, $sql, $params)->fetchAll();

$customers = [];
foreach ($rows as $row) {
  $total = (int)$row['total_count'];
  if ($total >= 3) {
    $category = 'loyal';
  } elseif ($total === 2) {
    $category = 'returning';
  } else {
    $category = 'new';
  }
  if ($categoryFilter && $category !== $categoryFilter) continue;
  $statusList = $row['statuses'] ? explode(',', $row['statuses']) : [];
  if ($statusFilter && !in_array($statusFilter, $statusList, true)) continue;
  $customers[] = $row + ['category_calc' => $category, 'status_list' => $statusList];
}

$categoryLabels = [
  'new'       => 'Yeni Müşteri',
  'returning' => 'Tekrar Gelen',
  'loyal'     => 'Sadık Müşteri',
];

admin_render_header('Müşteriler', 'customers');
?>
<form method="get" class="grid md:grid-cols-4 gap-4">
  <div>
    <label class="text-xs text-slate-500">Personel</label>
    <select name="provider" class="mt-1 border rounded-lg w-full p-2">
      <option value="0">Tümü</option>
      <?php foreach ($providers as $prov): ?>
        <option value="<?= (int)$prov['id'] ?>" <?= $providerFilter === (int)$prov['id'] ? 'selected' : '' ?>><?= h($prov['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="text-xs text-slate-500">Durum</label>
    <select name="status" class="mt-1 border rounded-lg w-full p-2">
      <option value="">Tümü</option>
      <?php foreach ($statusOptions as $key => $label): ?>
        <option value="<?= h($key) ?>" <?= $statusFilter === $key ? 'selected' : '' ?>><?= h($label) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="text-xs text-slate-500">Kategori</label>
    <select name="category" class="mt-1 border rounded-lg w-full p-2">
      <option value="">Tümü</option>
      <?php foreach ($categoryLabels as $key => $label): ?>
        <option value="<?= h($key) ?>" <?= $categoryFilter === $key ? 'selected' : '' ?>><?= h($label) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="text-xs text-slate-500">Arama</label>
    <input type="text" name="search" value="<?= h($search) ?>" class="mt-1 border rounded-lg w-full p-2" placeholder="Ad, telefon veya e-posta">
  </div>
  <div class="md:col-span-4 flex items-center gap-3">
    <button class="px-4 py-2 rounded-lg bg-slate-900 text-white">Filtrele</button>
    <a href="/admin/customers.php" class="px-3 py-2 rounded-lg border">Sıfırla</a>
  </div>
</form>

<div class="border rounded-xl bg-white overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
      <tr>
        <th class="px-4 py-2 text-left">Müşteri</th>
        <th class="px-4 py-2 text-left">İletişim</th>
        <th class="px-4 py-2 text-left">Toplam</th>
        <th class="px-4 py-2 text-left">Son Randevu</th>
        <th class="px-4 py-2 text-left">Kategori</th>
        <th class="px-4 py-2 text-left">Durumlar</th>
        <th class="px-4 py-2 text-left">Personel</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($customers as $customer): ?>
      <tr class="border-t">
        <td class="px-4 py-3 text-slate-700">
          <a href="/admin/customer.php?key=<?= urlencode($customer['customer_key']) ?>" class="text-slate-800 hover:text-slate-500 font-medium">
            <?= h($customer['full_name']) ?>
          </a>
        </td>
        <td class="px-4 py-3 text-slate-600">
          <?= h($customer['phone']) ?><br>
          <?php if (!empty($customer['email'])): ?><?= h($customer['email']) ?><?php endif; ?>
        </td>
        <td class="px-4 py-3 text-slate-600">
          <?= (int)$customer['total_count'] ?> randevu
        </td>
        <td class="px-4 py-3 text-slate-600">
          <?= h(date('d.m.Y', strtotime($customer['last_date']))) ?>
        </td>
        <td class="px-4 py-3 text-slate-600">
          <span class="inline-flex px-2 py-1 rounded bg-slate-100 text-slate-600 text-xs"><?= h($categoryLabels[$customer['category_calc']] ?? $customer['category_calc']) ?></span>
        </td>
        <td class="px-4 py-3 text-slate-600">
          <?php foreach ($customer['status_list'] as $status): ?>
            <span class="inline-flex px-2 py-1 mr-1 mb-1 rounded bg-slate-100 text-slate-600 text-xs"><?= h($statusOptions[$status] ?? $status) ?></span>
          <?php endforeach; ?>
        </td>
        <td class="px-4 py-3 text-slate-600"><?= h($customer['providers']) ?></td>
      </tr>
    <?php endforeach; if (empty($customers)): ?>
      <tr><td colspan="7" class="px-4 py-6 text-center text-slate-400">Kayıt bulunamadı.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>
<?php
admin_render_footer();
