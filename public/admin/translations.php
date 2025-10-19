<?php
require_once __DIR__.'/bootstrap.php';
must_login(['translations']);

$langs = available_languages($pdo);
$defaultCode = default_language_code($pdo);
$selectedCode = $_GET['lang'] ?? $defaultCode;
if (!isset($langs[$selectedCode])) {
  $selectedCode = $defaultCode;
}

$langRow = q($pdo, 'SELECT * FROM languages WHERE code=?', [$selectedCode])->fetch();
$langId = $langRow['id'] ?? null;

$defaults = translation_defaults();
$existing = [];
if ($langId) {
  $rows = q($pdo, 'SELECT `key`, `value` FROM translations WHERE lang_id=?', [$langId])->fetchAll();
  foreach ($rows as $row) {
    $existing[$row['key']] = $row['value'];
  }
}
$allKeys = array_unique(array_merge(array_keys($defaults), array_keys($existing)));
sort($allKeys);

if (is_post() && $langId) {
  csrf_check();
  $action = $_POST['action'] ?? 'save';
  if ($action === 'save') {
    $values = $_POST['translations'] ?? [];
    foreach ($allKeys as $key) {
      $val = $values[$key] ?? '';
      q($pdo, 'INSERT INTO translations (lang_id, `key`, `value`) VALUES (?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)', [$langId, $key, $val !== '' ? $val : $defaults[$key] ?? '']);
    }
    clear_translation_cache();
    set_flash('success', 'Çeviriler güncellendi.');
  } elseif ($action === 'add_key') {
    $newKey = trim($_POST['new_key'] ?? '');
    $newValue = trim($_POST['new_value'] ?? '');
    if ($newKey !== '') {
      q($pdo, 'INSERT INTO translations (lang_id, `key`, `value`) VALUES (?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)', [$langId, $newKey, $newValue]);
      clear_translation_cache();
      set_flash('success', 'Yeni anahtar eklendi.');
    }
  }
  redirect('/admin/translations.php?lang='.$selectedCode);
}

admin_render_header('Çeviriler', 'translations');
?>
<form method="get" class="mb-4">
  <label class="text-sm text-slate-600">Dil seçin:
    <select name="lang" class="border rounded-lg p-2" onchange="this.form.submit()">
      <?php foreach ($langs as $code => $lang): ?>
        <option value="<?= h($code) ?>" <?= $code === $selectedCode ? 'selected' : '' ?>><?= h($lang['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
</form>

<section class="border rounded-xl bg-white p-5 mb-6">
  <h2 class="text-lg font-semibold text-slate-700 mb-3">Çeviri Anahtarları</h2>
  <form method="post" class="space-y-4">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <?php foreach ($allKeys as $key): $value = $existing[$key] ?? ($defaults[$key] ?? ''); ?>
      <div>
        <label class="text-xs text-slate-500"><?= h($key) ?></label>
        <textarea name="translations[<?= h($key) ?>]" rows="2" class="mt-1 border rounded-lg w-full p-2 text-sm"><?= h($value) ?></textarea>
      </div>
    <?php endforeach; ?>
    <button class="px-4 py-2 rounded-lg bg-slate-900 text-white">Kaydet</button>
  </form>
</section>

<section class="border rounded-xl bg-white p-5">
  <h2 class="text-lg font-semibold text-slate-700 mb-3">Yeni Anahtar Ekle</h2>
  <form method="post" class="grid md:grid-cols-2 gap-4">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add_key">
    <label class="text-sm text-slate-600">Anahtar
      <input type="text" name="new_key" class="mt-1 border rounded-lg w-full p-2" required>
    </label>
    <label class="text-sm text-slate-600">Değer
      <input type="text" name="new_value" class="mt-1 border rounded-lg w-full p-2">
    </label>
    <div class="md:col-span-2">
      <button class="px-4 py-2 rounded-lg bg-slate-900 text-white">Ekle</button>
    </div>
  </form>
</section>
<?php
admin_render_footer();
