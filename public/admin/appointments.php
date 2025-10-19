<?php
require_once __DIR__.'/bootstrap.php';
must_login(['appointments']);

$statusOptions = appointment_statuses();
$providers = q($pdo, 'SELECT id, name FROM providers ORDER BY name')->fetchAll();
$langs = available_languages($pdo);

if (is_post() && isset($_POST['appointment_id'])) {
  csrf_check();
  $id = (int)$_POST['appointment_id'];
  $status = $_POST['status'] ?? '';
  $category = trim($_POST['category'] ?? '');
  $adminNote = trim($_POST['admin_note'] ?? '');
  if (!isset($statusOptions[$status])) {
    set_flash('error', 'Geçersiz durum seçildi.');
  } else {
    q($pdo, 'UPDATE appointments SET status=?, category=?, admin_note=? WHERE id=?', [$status, $category !== '' ? $category : null, $adminNote !== '' ? $adminNote : null, $id]);
    set_flash('success', 'Randevu güncellendi.');
  }
  $query = $_GET;
  redirect('/admin/appointments.php'.(!empty($query) ? '?'.http_build_query($query) : ''));
}

$where = [];
$params = [];

$statusFilter = $_GET['status'] ?? '';
if ($statusFilter !== '' && isset($statusOptions[$statusFilter])) {
  $where[] = 'a.status = ?';
  $params[] = $statusFilter;
}

$providerFilter = (int)($_GET['provider'] ?? 0);
if ($providerFilter > 0) {
  $where[] = 'a.provider_id = ?';
  $params[] = $providerFilter;
}

$categoryFilter = trim($_GET['category'] ?? '');
if ($categoryFilter !== '') {
  $where[] = 'a.category = ?';
  $params[] = $categoryFilter;
}

$langFilter = $_GET['lang'] ?? '';
if ($langFilter !== '' && isset($langs[$langFilter])) {
  $where[] = 'a.lang_code = ?';
  $params[] = $langFilter;
}

$dateFrom = $_GET['from'] ?? '';
$dateTo   = $_GET['to'] ?? '';
if ($dateFrom && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
  $where[] = 'a.app_date >= ?';
  $params[] = $dateFrom;
}
if ($dateTo && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
  $where[] = 'a.app_date <= ?';
  $params[] = $dateTo;
}

$search = trim($_GET['search'] ?? '');
if ($search !== '') {
  $like = '%'.$search.'%';
  $where[] = '(a.full_name LIKE ? OR a.phone LIKE ? OR a.email LIKE ?)';
  array_push($params, $like, $like, $like);
}

$sql = 'SELECT a.*, p.name provider FROM appointments a JOIN providers p ON p.id = a.provider_id';
if ($where) {
  $sql .= ' WHERE '.implode(' AND ', $where);
}
$sql .= ' ORDER BY a.app_date DESC, a.app_time DESC LIMIT 150';
$rows = q($pdo, $sql, $params)->fetchAll();

admin_render_header('Randevular', 'appointments');
?>
<form method="get" class="grid md:grid-cols-6 gap-4">
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
    <label class="text-xs text-slate-500">Personel</label>
    <select name="provider" class="mt-1 border rounded-lg w-full p-2">
      <option value="0">Tümü</option>
      <?php foreach ($providers as $prov): ?>
        <option value="<?= (int)$prov['id'] ?>" <?= $providerFilter === (int)$prov['id'] ? 'selected' : '' ?>><?= h($prov['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="text-xs text-slate-500">Kategori</label>
    <input type="text" name="category" value="<?= h($categoryFilter) ?>" class="mt-1 border rounded-lg w-full p-2" placeholder="Örn. VIP">
  </div>
  <div>
    <label class="text-xs text-slate-500">Dil</label>
    <select name="lang" class="mt-1 border rounded-lg w-full p-2">
      <option value="">Tümü</option>
      <?php foreach ($langs as $code => $lang): ?>
        <option value="<?= h($code) ?>" <?= $langFilter === $code ? 'selected' : '' ?>><?= h($lang['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="text-xs text-slate-500">Başlangıç</label>
    <input type="date" name="from" value="<?= h($dateFrom) ?>" class="mt-1 border rounded-lg w-full p-2">
  </div>
  <div>
    <label class="text-xs text-slate-500">Bitiş</label>
    <input type="date" name="to" value="<?= h($dateTo) ?>" class="mt-1 border rounded-lg w-full p-2">
  </div>
  <div class="md:col-span-6 flex items-center gap-3">
    <input type="text" name="search" value="<?= h($search) ?>" placeholder="Ad, telefon veya e-posta" class="border rounded-lg w-full p-2">
    <button class="px-4 py-2 rounded-lg bg-slate-900 text-white">Filtrele</button>
    <a href="/admin/appointments.php" class="px-3 py-2 rounded-lg border">Sıfırla</a>
  </div>
</form>

<div class="border rounded-xl bg-white overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
      <tr>
        <th class="px-4 py-2 text-left">#</th>
        <th class="px-4 py-2 text-left">Tarih</th>
        <th class="px-4 py-2 text-left">Saat</th>
        <th class="px-4 py-2 text-left">Müşteri</th>
        <th class="px-4 py-2 text-left">Personel</th>
        <th class="px-4 py-2 text-left">Durum</th>
        <th class="px-4 py-2 text-left">Kategori</th>
        <th class="px-4 py-2 text-left">Dil</th>
        <th class="px-4 py-2 text-left">Not</th>
        <th class="px-4 py-2 text-left">İşlem</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $row): ?>
      <tr class="border-t align-top">
        <td class="px-4 py-3 text-slate-600">#<?= (int)$row['id'] ?></td>
        <td class="px-4 py-3 text-slate-600"><?= h(date('d.m.Y', strtotime($row['app_date']))) ?></td>
        <td class="px-4 py-3 text-slate-600"><?= h($row['app_time']) ?></td>
        <td class="px-4 py-3 text-slate-700">
          <div><?= h($row['full_name']) ?></div>
          <div class="text-xs text-slate-500"><?= h($row['phone']) ?></div>
          <?php if ($row['email']): ?><div class="text-xs text-slate-500"><?= h($row['email']) ?></div><?php endif; ?>
        </td>
        <td class="px-4 py-3 text-slate-600"><?= h($row['provider']) ?></td>
        <td class="px-4 py-3">
          <span class="inline-flex px-2 py-1 rounded bg-slate-100 text-slate-600 text-xs"><?= h($statusOptions[$row['status']] ?? $row['status']) ?></span>
        </td>
        <td class="px-4 py-3 text-slate-600"><?= h($row['category'] ?? '') ?></td>
        <td class="px-4 py-3 text-slate-600"><?= h($row['lang_code'] ?? '-') ?></td>
        <td class="px-4 py-3 text-slate-600">
          <?php if (!empty($row['note'])): ?>
            <div class="text-xs text-slate-500">Müşteri: <?= nl2br(h($row['note'])) ?></div>
          <?php endif; ?>
          <?php if (!empty($row['admin_note'])): ?>
            <div class="text-xs text-amber-600 mt-2">Yönetici: <?= nl2br(h($row['admin_note'])) ?></div>
          <?php endif; ?>
        </td>
        <td class="px-4 py-3">
          <form method="post" class="space-y-2">
            <?= csrf_field() ?>
            <input type="hidden" name="appointment_id" value="<?= (int)$row['id'] ?>">
            <select name="status" class="border rounded-lg w-full p-2 text-sm">
              <?php foreach ($statusOptions as $key => $label): ?>
                <option value="<?= h($key) ?>" <?= $row['status'] === $key ? 'selected' : '' ?>><?= h($label) ?></option>
              <?php endforeach; ?>
            </select>
            <input type="text" name="category" value="<?= h($row['category'] ?? '') ?>" class="border rounded-lg w-full p-2 text-sm" placeholder="Kategori">
            <textarea name="admin_note" rows="2" class="border rounded-lg w-full p-2 text-sm" placeholder="Yönetici notu"><?= h($row['admin_note'] ?? '') ?></textarea>
            <button class="px-3 py-2 rounded-lg bg-slate-900 text-white text-sm w-full">Kaydet</button>
          </form>
        </td>
      </tr>
    <?php endforeach; if (empty($rows)): ?>
      <tr><td colspan="10" class="px-4 py-6 text-center text-slate-400">Kayıt bulunamadı.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>
<?php
admin_render_footer();
