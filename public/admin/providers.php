<?php
require_once __DIR__.'/bootstrap.php';
must_login(['providers']);

$canManageAvailability = has_permission('availability');

if (!is_dir(__DIR__.'/../uploads/providers')) {
  @mkdir(__DIR__.'/../uploads/providers', 0777, true);
}

if (is_post()) {
  csrf_check();
  $action = $_POST['action'] ?? '';
  if ($action === 'save_provider') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $active = isset($_POST['active']) ? 1 : 0;
    $sort = (int)($_POST['sort'] ?? 0);
    $primary = trim($_POST['primary_color'] ?? '#0284c7');
    $secondary = trim($_POST['secondary_color'] ?? '#4f46e5');
    $duration = max(5, (int)($_POST['default_duration'] ?? 30));
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if ($name === '') {
      set_flash('error', 'İsim zorunludur.');
    } else {
      if ($id > 0) {
        q($pdo, 'UPDATE providers SET name=?, bio=?, active=?, sort=?, primary_color=?, secondary_color=?, default_duration=?, email=?, phone=? WHERE id=?', [$name, $bio !== '' ? $bio : null, $active, $sort, $primary, $secondary, $duration, $email !== '' ? $email : null, $phone !== '' ? $phone : null, $id]);
      } else {
        q($pdo, 'INSERT INTO providers (name, bio, active, sort, primary_color, secondary_color, default_duration, email, phone) VALUES (?,?,?,?,?,?,?,?,?)', [$name, $bio !== '' ? $bio : null, $active, $sort, $primary, $secondary, $duration, $email !== '' ? $email : null, $phone !== '' ? $phone : null]);
        $id = (int)$pdo->lastInsertId();
      }
      if (!empty($_FILES['image']['tmp_name'])) {
        $file = $_FILES['image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp'])) {
          $filename = 'provider-'.$id.'-'.uniqid().'.'.$ext;
          $dest = __DIR__.'/../uploads/providers/'.$filename;
          if (move_uploaded_file($file['tmp_name'], $dest)) {
            q($pdo, 'UPDATE providers SET image=? WHERE id=?', [$filename, $id]);
          }
        }
      }
      set_flash('success', 'Kayıt güncellendi.');
      redirect('/admin/providers.php?id='.$id);
    }
  } elseif ($action === 'update_availability' && $canManageAvailability) {
    $id = (int)($_POST['id'] ?? 0);
    $availability = $_POST['availability'] ?? [];
    for ($day = 1; $day <= 7; $day++) {
      $row = $availability[$day] ?? [];
      $start = trim($row['start'] ?? '');
      $end = trim($row['end'] ?? '');
      $slot = max(5, (int)($row['slot'] ?? 30));
      if ($start === '' || $end === '') {
        q($pdo, 'DELETE FROM provider_availability WHERE provider_id=? AND weekday=?', [$id, $day]);
      } else {
        q($pdo, 'INSERT INTO provider_availability (provider_id, weekday, start_time, end_time, slot_minutes) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE start_time=VALUES(start_time), end_time=VALUES(end_time), slot_minutes=VALUES(slot_minutes)', [$id, $day, $start, $end, $slot]);
      }
    }
    set_flash('success', 'Müsaitlik güncellendi.');
    redirect('/admin/providers.php?id='.$id);
  } elseif ($action === 'add_timeoff' && $canManageAvailability) {
    $id = (int)($_POST['id'] ?? 0);
    $type = $_POST['timeoff_type'] ?? 'single';
    $date = $_POST['date'] ?? null;
    $weekday = (int)($_POST['weekday'] ?? 0);
    $start = trim($_POST['start_time'] ?? '');
    $end = trim($_POST['end_time'] ?? '');
    if ($start && $end) {
      $isRecurring = $type === 'weekly' ? 1 : 0;
      if ($isRecurring && $weekday >=1 && $weekday <=7) {
        q($pdo, 'INSERT INTO provider_timeoffs (provider_id, weekday, start_time, end_time, is_recurring) VALUES (?,?,?,?,1)', [$id, $weekday, $start, $end]);
      } elseif (!$isRecurring && $date) {
        q($pdo, 'INSERT INTO provider_timeoffs (provider_id, date, start_time, end_time, is_recurring) VALUES (?,?,?,?,0)', [$id, $date, $start, $end]);
      }
      set_flash('success', 'Müsaitlik aralığı eklendi.');
    }
    redirect('/admin/providers.php?id='.$id);
  } elseif ($action === 'delete_timeoff' && $canManageAvailability) {
    $id = (int)($_POST['id'] ?? 0);
    $timeoffId = (int)($_POST['timeoff_id'] ?? 0);
    q($pdo, 'DELETE FROM provider_timeoffs WHERE id=? AND provider_id=?', [$timeoffId, $id]);
    set_flash('success', 'Kayıt silindi.');
    redirect('/admin/providers.php?id='.$id);
  }
}

$providers = q($pdo, 'SELECT * FROM providers ORDER BY sort, name')->fetchAll();
$selectedId = (int)($_GET['id'] ?? ($providers[0]['id'] ?? 0));
$selected = null;
if ($selectedId) {
  $selected = q($pdo, 'SELECT * FROM providers WHERE id=?', [$selectedId])->fetch();
}

$availabilityMap = [];
if ($selected) {
  $availabilityRows = q($pdo, 'SELECT * FROM provider_availability WHERE provider_id=?', [$selected['id']])->fetchAll();
  foreach ($availabilityRows as $row) {
    $availabilityMap[(int)$row['weekday']] = $row;
  }
  $timeoffs = q($pdo, 'SELECT * FROM provider_timeoffs WHERE provider_id=? ORDER BY date IS NULL, date, weekday', [$selected['id']])->fetchAll();
} else {
  $timeoffs = [];
}

$weekdays = [1=>'Pazartesi',2=>'Salı',3=>'Çarşamba',4=>'Perşembe',5=>'Cuma',6=>'Cumartesi',7=>'Pazar'];

admin_render_header('Randevu Verenler', 'providers');
?>
<div class="grid lg:grid-cols-3 gap-6">
  <div class="lg:col-span-1">
    <h2 class="text-lg font-semibold text-slate-700 mb-3">Personeller</h2>
    <div class="space-y-3">
      <?php foreach ($providers as $prov): ?>
        <a href="/admin/providers.php?id=<?= (int)$prov['id'] ?>" class="block border rounded-xl px-4 py-3 <?= $selectedId === (int)$prov['id'] ? 'bg-slate-900 text-white' : 'bg-white' ?>">
          <div class="font-semibold"><?= h($prov['name']) ?></div>
          <div class="text-xs <?= $selectedId === (int)$prov['id'] ? 'text-slate-200' : 'text-slate-500' ?>"><?= $prov['active'] ? 'Aktif' : 'Pasif' ?></div>
        </a>
      <?php endforeach; ?>
      <a href="/admin/providers.php?id=0" class="block border rounded-xl px-4 py-3 bg-emerald-50 text-emerald-700">+ Yeni personel</a>
    </div>
  </div>
  <div class="lg:col-span-2 space-y-6">
    <?php if ($selected || $selectedId === 0): ?>
      <section class="border rounded-xl bg-white p-5">
        <h3 class="text-lg font-semibold text-slate-700 mb-4">Personel Bilgileri</h3>
        <form method="post" enctype="multipart/form-data" class="grid md:grid-cols-2 gap-4">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="save_provider">
          <input type="hidden" name="id" value="<?= (int)($selected['id'] ?? 0) ?>">
          <label class="text-sm text-slate-600">Ad Soyad
            <input type="text" name="name" value="<?= h($selected['name'] ?? '') ?>" class="mt-1 border rounded-lg w-full p-2" required>
          </label>
          <label class="text-sm text-slate-600">Sıralama
            <input type="number" name="sort" value="<?= h((string)($selected['sort'] ?? 0)) ?>" class="mt-1 border rounded-lg w-full p-2">
          </label>
          <label class="text-sm text-slate-600">E-posta
            <input type="email" name="email" value="<?= h($selected['email'] ?? '') ?>" class="mt-1 border rounded-lg w-full p-2">
          </label>
          <label class="text-sm text-slate-600">Telefon
            <input type="text" name="phone" value="<?= h($selected['phone'] ?? '') ?>" class="mt-1 border rounded-lg w-full p-2">
          </label>
          <label class="text-sm text-slate-600">Varsayılan Süre (dk)
            <input type="number" name="default_duration" value="<?= h((string)($selected['default_duration'] ?? 30)) ?>" class="mt-1 border rounded-lg w-full p-2">
          </label>
          <label class="text-sm text-slate-600">Durum
            <input type="checkbox" name="active" value="1" <?= !empty($selected['active']) ? 'checked' : '' ?>> Aktif
          </label>
          <label class="text-sm text-slate-600 md:col-span-2">Açıklama
            <textarea name="bio" rows="3" class="mt-1 border rounded-lg w-full p-2"><?= h($selected['bio'] ?? '') ?></textarea>
          </label>
          <label class="text-sm text-slate-600">Birincil Renk
            <input type="color" name="primary_color" value="<?= h($selected['primary_color'] ?? '#0284c7') ?>" class="mt-1 w-16 h-10 border rounded">
          </label>
          <label class="text-sm text-slate-600">İkincil Renk
            <input type="color" name="secondary_color" value="<?= h($selected['secondary_color'] ?? '#4f46e5') ?>" class="mt-1 w-16 h-10 border rounded">
          </label>
          <label class="text-sm text-slate-600 md:col-span-2">Fotoğraf
            <input type="file" name="image" class="mt-1">
          </label>
          <?php if (!empty($selected['image'])): ?>
            <div class="md:col-span-2">
              <img src="/uploads/providers/<?= h($selected['image']) ?>" alt="" loading="lazy" decoding="async" class="w-32 h-32 object-cover rounded-lg border">
            </div>
          <?php endif; ?>
          <div class="md:col-span-2">
            <button class="px-4 py-2 rounded-lg bg-slate-900 text-white">Kaydet</button>
          </div>
        </form>
      </section>
    <?php endif; ?>

    <?php if ($selected && $canManageAvailability): ?>
      <section class="border rounded-xl bg-white p-5">
        <h3 class="text-lg font-semibold text-slate-700 mb-4">Haftalık Müsaitlik</h3>
        <form method="post" class="space-y-3">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="update_availability">
          <input type="hidden" name="id" value="<?= (int)$selected['id'] ?>">
          <?php foreach ($weekdays as $day => $label): $row = $availabilityMap[$day] ?? null; ?>
            <div class="grid md:grid-cols-4 gap-3 items-end border rounded-lg p-3">
              <div class="text-sm font-medium text-slate-600 md:col-span-1"><?= h($label) ?></div>
              <div>
                <label class="text-xs text-slate-500">Başlangıç</label>
                <input type="time" name="availability[<?= $day ?>][start]" value="<?= h($row['start_time'] ?? '') ?>" class="border rounded-lg w-full p-2">
              </div>
              <div>
                <label class="text-xs text-slate-500">Bitiş</label>
                <input type="time" name="availability[<?= $day ?>][end]" value="<?= h($row['end_time'] ?? '') ?>" class="border rounded-lg w-full p-2">
              </div>
              <div>
                <label class="text-xs text-slate-500">Süre (dk)</label>
                <input type="number" name="availability[<?= $day ?>][slot]" value="<?= h((string)($row['slot_minutes'] ?? $selected['default_duration'])) ?>" class="border rounded-lg w-full p-2">
              </div>
            </div>
          <?php endforeach; ?>
          <button class="px-4 py-2 rounded-lg bg-slate-900 text-white">Kaydet</button>
        </form>
      </section>

      <section class="border rounded-xl bg-white p-5">
        <h3 class="text-lg font-semibold text-slate-700 mb-4">Kapalı Aralıklar</h3>
        <form method="post" class="grid md:grid-cols-4 gap-4 items-end">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="add_timeoff">
          <input type="hidden" name="id" value="<?= (int)$selected['id'] ?>">
          <div>
            <label class="text-xs text-slate-500">Tip</label>
            <select name="timeoff_type" class="border rounded-lg w-full p-2">
              <option value="single">Tek Gün</option>
              <option value="weekly">Her Hafta</option>
            </select>
          </div>
          <div>
            <label class="text-xs text-slate-500">Tarih</label>
            <input type="date" name="date" class="border rounded-lg w-full p-2">
          </div>
          <div>
            <label class="text-xs text-slate-500">Gün (1-7)</label>
            <input type="number" name="weekday" min="1" max="7" class="border rounded-lg w-full p-2">
          </div>
          <div class="md:col-span-2">
            <label class="text-xs text-slate-500">Başlangıç</label>
            <input type="time" name="start_time" class="border rounded-lg w-full p-2">
          </div>
          <div class="md:col-span-2">
            <label class="text-xs text-slate-500">Bitiş</label>
            <input type="time" name="end_time" class="border rounded-lg w-full p-2">
          </div>
          <div class="md:col-span-4">
            <button class="px-4 py-2 rounded-lg bg-slate-900 text-white">Ekle</button>
          </div>
        </form>
        <div class="mt-4 overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
              <tr>
                <th class="px-4 py-2 text-left">Tip</th>
                <th class="px-4 py-2 text-left">Gün/Tarih</th>
                <th class="px-4 py-2 text-left">Saat</th>
                <th class="px-4 py-2 text-left">İşlem</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($timeoffs as $item): ?>
              <tr class="border-t">
                <td class="px-4 py-2 text-slate-600"><?= $item['is_recurring'] ? 'Haftalık' : 'Tek gün' ?></td>
                <td class="px-4 py-2 text-slate-600"><?= $item['is_recurring'] ? $weekdays[$item['weekday']] : date('d.m.Y', strtotime($item['date'])) ?></td>
                <td class="px-4 py-2 text-slate-600"><?= h($item['start_time'].' - '.$item['end_time']) ?></td>
                <td class="px-4 py-2">
                  <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete_timeoff">
                    <input type="hidden" name="id" value="<?= (int)$selected['id'] ?>">
                    <input type="hidden" name="timeoff_id" value="<?= (int)$item['id'] ?>">
                    <button class="px-3 py-1 rounded bg-rose-500 text-white text-xs">Sil</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; if (empty($timeoffs)): ?>
              <tr><td colspan="4" class="px-4 py-4 text-center text-slate-400">Kayıt yok.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    <?php elseif (!$canManageAvailability): ?>
      <div class="border rounded-xl bg-white p-5 text-slate-500">Müsaitlik düzenleme yetkiniz yok.</div>
    <?php endif; ?>
  </div>
</div>
<?php
admin_render_footer();
