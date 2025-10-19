<?php
require_once __DIR__.'/bootstrap.php';
must_login(['dashboard']);

$totalAppointments = (int)q($pdo, 'SELECT COUNT(*) FROM appointments')->fetchColumn();
$upcomingCount     = (int)q($pdo, 'SELECT COUNT(*) FROM appointments WHERE app_date >= CURDATE()')->fetchColumn();
$providerCount     = (int)q($pdo, 'SELECT COUNT(*) FROM providers WHERE active=1')->fetchColumn();
$customerCount     = (int)q($pdo, 'SELECT COUNT(DISTINCT COALESCE(NULLIF(email, ""), phone)) FROM appointments')->fetchColumn();

$upcoming = q($pdo, 'SELECT a.id, a.app_date, a.app_time, a.full_name, p.name provider FROM appointments a JOIN providers p ON p.id=a.provider_id WHERE a.app_date >= CURDATE() ORDER BY a.app_date, a.app_time LIMIT 8')->fetchAll();
$recent   = q($pdo, 'SELECT a.id, a.created_at, a.full_name, a.status, p.name provider FROM appointments a JOIN providers p ON p.id=a.provider_id ORDER BY a.created_at DESC LIMIT 8')->fetchAll();

admin_render_header('Gösterge Paneli', 'dashboard');
?>
<div class="grid md:grid-cols-4 gap-4">
  <div class="p-4 rounded-xl border bg-white">
    <div class="text-sm text-slate-500">Toplam Randevu</div>
    <div class="text-2xl font-semibold text-slate-800"><?= $totalAppointments ?></div>
  </div>
  <div class="p-4 rounded-xl border bg-white">
    <div class="text-sm text-slate-500">Yaklaşan Randevu</div>
    <div class="text-2xl font-semibold text-slate-800"><?= $upcomingCount ?></div>
  </div>
  <div class="p-4 rounded-xl border bg-white">
    <div class="text-sm text-slate-500">Aktif Personel</div>
    <div class="text-2xl font-semibold text-slate-800"><?= $providerCount ?></div>
  </div>
  <div class="p-4 rounded-xl border bg-white">
    <div class="text-sm text-slate-500">Müşteri</div>
    <div class="text-2xl font-semibold text-slate-800"><?= $customerCount ?></div>
  </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
  <div>
    <h2 class="text-lg font-semibold text-slate-700 mb-3">Yaklaşan Randevular</h2>
    <div class="border rounded-xl bg-white">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
          <tr>
            <th class="text-left px-4 py-2">Tarih</th>
            <th class="text-left px-4 py-2">Saat</th>
            <th class="text-left px-4 py-2">Ad Soyad</th>
            <th class="text-left px-4 py-2">Personel</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($upcoming as $item): ?>
          <tr class="border-t">
            <td class="px-4 py-2 text-slate-600"><?= h(date('d.m.Y', strtotime($item['app_date']))) ?></td>
            <td class="px-4 py-2 text-slate-600"><?= h($item['app_time']) ?></td>
            <td class="px-4 py-2 text-slate-700"><?= h($item['full_name']) ?></td>
            <td class="px-4 py-2 text-slate-600"><?= h($item['provider']) ?></td>
          </tr>
        <?php endforeach; if (empty($upcoming)): ?>
          <tr><td colspan="4" class="px-4 py-6 text-center text-slate-400">Yaklaşan randevu yok.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div>
    <h2 class="text-lg font-semibold text-slate-700 mb-3">Son İşlemler</h2>
    <div class="border rounded-xl bg-white">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
          <tr>
            <th class="text-left px-4 py-2">#</th>
            <th class="text-left px-4 py-2">Müşteri</th>
            <th class="text-left px-4 py-2">Durum</th>
            <th class="text-left px-4 py-2">Personel</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($recent as $item): $statuses = appointment_statuses(); ?>
          <tr class="border-t">
            <td class="px-4 py-2 text-slate-600">#<?= (int)$item['id'] ?></td>
            <td class="px-4 py-2 text-slate-700"><?= h($item['full_name']) ?></td>
            <td class="px-4 py-2"><span class="inline-flex px-2 py-1 rounded bg-slate-100 text-slate-600 text-xs"><?= h($statuses[$item['status']] ?? $item['status']) ?></span></td>
            <td class="px-4 py-2 text-slate-600"><?= h($item['provider']) ?></td>
          </tr>
        <?php endforeach; if (empty($recent)): ?>
          <tr><td colspan="4" class="px-4 py-6 text-center text-slate-400">Kayıt bulunamadı.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php
admin_render_footer();
