<?php
require_once __DIR__.'/bootstrap.php';
must_login(['languages']);

if (is_post()) {
  csrf_check();
  $action = $_POST['action'] ?? '';
  if ($action === 'add') {
    $code = strtolower(trim($_POST['code'] ?? ''));
    $name = trim($_POST['name'] ?? '');
    if ($code === '' || $name === '') {
      set_flash('error', 'Kod ve ad zorunludur.');
    } else {
      q($pdo, 'INSERT INTO languages (code, name) VALUES (?,?) ON DUPLICATE KEY UPDATE name=VALUES(name)', [$code, $name]);
      clear_translation_cache();
      set_flash('success', 'Dil kaydedildi.');
    }
    redirect('/admin/languages.php');
  } elseif ($action === 'default') {
    $code = $_POST['code'] ?? '';
    q($pdo, 'UPDATE languages SET is_default = (code = ?)', [$code]);
    clear_translation_cache();
    set_flash('success', 'Varsayılan dil güncellendi.');
    redirect('/admin/languages.php');
  } elseif ($action === 'delete') {
    $code = $_POST['code'] ?? '';
    $isDefault = (int)q($pdo, 'SELECT is_default FROM languages WHERE code=?', [$code])->fetchColumn();
    if ($isDefault) {
      set_flash('error', 'Varsayılan dil silinemez.');
    } else {
      q($pdo, 'DELETE FROM languages WHERE code=?', [$code]);
      clear_translation_cache();
      set_flash('success', 'Dil silindi.');
    }
    redirect('/admin/languages.php');
  }
}

$langs = available_languages($pdo);
$defaultCode = default_language_code($pdo);

admin_render_header('Dil Yönetimi', 'languages');
?>
<div class="grid lg:grid-cols-2 gap-6">
  <section class="border rounded-xl bg-white p-5">
    <h2 class="text-lg font-semibold text-slate-700 mb-3">Dil Ekle</h2>
    <form method="post" class="space-y-3">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add">
      <label class="text-sm text-slate-600">Dil Kodu (ör: tr, en)
        <input type="text" name="code" class="mt-1 border rounded-lg w-full p-2" maxlength="5" required>
      </label>
      <label class="text-sm text-slate-600">Dil Adı
        <input type="text" name="name" class="mt-1 border rounded-lg w-full p-2" required>
      </label>
      <button class="px-4 py-2 rounded-lg bg-slate-900 text-white">Kaydet</button>
    </form>
  </section>

  <section class="border rounded-xl bg-white p-5">
    <h2 class="text-lg font-semibold text-slate-700 mb-3">Diller</h2>
    <div class="space-y-3">
      <?php foreach ($langs as $code => $lang): ?>
        <div class="border rounded-lg p-4 flex items-center justify-between">
          <div>
            <div class="font-semibold text-slate-700"><?= h($lang['name']) ?></div>
            <div class="text-xs text-slate-500"><?= h($code) ?><?= $lang['is_default'] ? ' • Varsayılan' : '' ?></div>
          </div>
          <div class="flex items-center gap-2">
            <?php if (!$lang['is_default']): ?>
              <form method="post" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="default">
                <input type="hidden" name="code" value="<?= h($code) ?>">
                <button class="px-3 py-1 rounded bg-emerald-500 text-white text-xs">Varsayılan Yap</button>
              </form>
              <form method="post" class="inline" onsubmit="return confirm('Silmek istediğinize emin misiniz?');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="code" value="<?= h($code) ?>">
                <button class="px-3 py-1 rounded bg-rose-500 text-white text-xs">Sil</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; if (empty($langs)): ?>
        <div class="text-slate-500">Dil bulunamadı.</div>
      <?php endif; ?>
    </div>
  </section>
</div>
<?php
admin_render_footer();
