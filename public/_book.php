<?php
$_SESSION['book'] = $_SESSION['book'] ?? [];
$pid = (int)($_GET['pid'] ?? ($_SESSION['book']['pid'] ?? 0));
if ($pid <= 0) {
  redirect('/');
}
$_SESSION['book']['pid'] = $pid;
if (is_post()) {
  csrf_check();
  $_SESSION['book']['full_name'] = trim($_POST['full_name'] ?? '');
  $_SESSION['book']['gender']    = trim($_POST['gender'] ?? '');
  $_SESSION['book']['birth']     = trim($_POST['birth'] ?? '');
  $_SESSION['book']['phone']     = trim($_POST['phone'] ?? '');
  $_SESSION['book']['email']     = trim($_POST['email'] ?? '');
  $_SESSION['book']['note']      = trim($_POST['note'] ?? '');
  redirect('/?p=date');
}
$prov = q($pdo, 'SELECT name,image FROM providers WHERE id=?', [$pid])->fetch();
$img  = ($prov && $prov['image'] && file_exists(__DIR__.'/uploads/providers/'.$prov['image']))
  ? '/uploads/providers/'.$prov['image']
  : 'https://placehold.co/96x96?text=+';
$g    = $_SESSION['book']['gender'] ?? '';
?>
<div class="p-6">
  <div class="flex items-center gap-4 mb-6">
    <img src="<?= $img ?>" class="w-16 h-16 rounded-xl object-cover border border-slate-200" loading="lazy" decoding="async" alt="<?= h($prov['name'] ?? '') ?>">
    <div>
      <div class="text-sm text-slate-500 uppercase tracking-wide"><?= h(t('book.provider')) ?></div>
      <div class="text-lg font-semibold text-slate-800"><?= h($prov['name'] ?? '') ?></div>
    </div>
  </div>
  <form method="post" class="grid md:grid-cols-2 gap-4">
    <?= csrf_field() ?>
    <label class="text-sm font-medium text-slate-600">
      <?= h(t('book.full_name')) ?> *
      <input required name="full_name" value="<?= h($_SESSION['book']['full_name'] ?? '') ?>" class="mt-1 border rounded w-full p-2 focus:outline-none focus:ring" autocomplete="name">
    </label>
    <label class="text-sm font-medium text-slate-600">
      <?= h(t('book.gender')) ?> *
      <select name="gender" required class="mt-1 border rounded w-full p-2 focus:outline-none focus:ring">
        <option value="">--</option>
        <?php $female = t('book.gender.female'); $male = t('book.gender.male'); ?>
        <option value="<?= h($female) ?>" <?= $g === $female ? 'selected' : '' ?>><?= h($female) ?></option>
        <option value="<?= h($male) ?>" <?= $g === $male ? 'selected' : '' ?>><?= h($male) ?></option>
      </select>
    </label>
    <label class="text-sm font-medium text-slate-600">
      <?= h(t('book.birth')) ?> *
      <input required name="birth" placeholder="GG.AA.YYYY" value="<?= h($_SESSION['book']['birth'] ?? '') ?>" class="mt-1 border rounded w-full p-2 focus:outline-none focus:ring" autocomplete="bday">
    </label>
    <label class="text-sm font-medium text-slate-600">
      <?= h(t('book.phone')) ?> *
      <input required name="phone" value="<?= h($_SESSION['book']['phone'] ?? '') ?>" class="mt-1 border rounded w-full p-2 focus:outline-none focus:ring" autocomplete="tel">
    </label>
    <label class="text-sm font-medium text-slate-600">
      <?= h(t('book.email')) ?>
      <input name="email" value="<?= h($_SESSION['book']['email'] ?? '') ?>" class="mt-1 border rounded w-full p-2 focus:outline-none focus:ring" autocomplete="email">
    </label>
    <label class="text-sm font-medium text-slate-600 md:col-span-2">
      <?= h(t('book.note')) ?>
      <textarea name="note" class="mt-1 border rounded w-full p-2 focus:outline-none focus:ring" rows="3"><?= h($_SESSION['book']['note'] ?? '') ?></textarea>
    </label>
    <div class="md:col-span-2 flex justify-between mt-2">
      <a href="/" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-600 hover:border-slate-400 transition">
        <?= h(t('btn.prev')) ?>
      </a>
      <button class="px-4 py-2 rounded-lg btn-primary">
        <?= h(t('btn.next')) ?>
      </button>
    </div>
  </form>
</div>
