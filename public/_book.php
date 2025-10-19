<?php
$_SESSION['book'] = $_SESSION['book'] ?? [];
$pid = (int)($_GET['pid'] ?? ($_SESSION['book']['pid'] ?? 0));
if ($pid <= 0) {
  redirect('/');
}
if (!is_post()) {
  unset($_SESSION['book']['duplicate_lock']);
}
$_SESSION['book']['pid'] = $pid;
$errors = [];
$recaptcha = recaptcha_settings($pdo);

$phoneCountry = $_SESSION['book']['phone_country'] ?? '+90';
$phoneLocal   = $_SESSION['book']['phone_local'] ?? '';
if (!$phoneLocal && !empty($_SESSION['book']['phone'])) {
  $parts = preg_split('/\s+/', trim((string)$_SESSION['book']['phone']));
  if ($parts && isset($parts[0]) && strpos($parts[0], '+') === 0) {
    $phoneCountry = $parts[0];
    $phoneLocal = trim(implode(' ', array_slice($parts, 1)));
  }
}

if (is_post()) {
  csrf_check();
  $fullName = trim($_POST['full_name'] ?? '');
  $gender   = trim($_POST['gender'] ?? '');
  $birth    = trim($_POST['birth'] ?? '');
  $phoneCountry = trim($_POST['phone_country'] ?? $phoneCountry);
  $phoneInput   = trim($_POST['phone'] ?? '');
  $email    = trim($_POST['email'] ?? '');
  $note     = trim($_POST['note'] ?? '');

  $_SESSION['book']['full_name'] = $fullName;
  $_SESSION['book']['gender']    = $gender;
  $_SESSION['book']['note']      = $note;
  $_SESSION['book']['email']     = $email;

  if (!preg_match('/^\+\d{1,4}$/', $phoneCountry)) {
    $phoneCountry = '+90';
  }

  if ($fullName === '') {
    $errors[] = 'Ad Soyad alanı zorunludur.';
  }

  $female = t('book.gender.female');
  $male   = t('book.gender.male');
  if (!in_array($gender, [$female, $male], true)) {
    $errors[] = 'Lütfen cinsiyet seçin.';
  }

  if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birth)) {
    $errors[] = 'Geçerli bir doğum tarihi seçin.';
  } else {
    $birthDate = strtotime($birth);
    if ($birthDate === false || $birthDate > time()) {
      $errors[] = 'Doğum tarihi bugünden ileri olamaz.';
    }
  }

  $phoneDigits = preg_replace('/\D+/', '', $phoneInput);
  if (strlen($phoneDigits) !== 10) {
    $errors[] = 'Telefon numarası 10 haneli olmalıdır.';
  }
  $formattedPhone = '';
  if (strlen($phoneDigits) === 10) {
    $formattedPhone = substr($phoneDigits, 0, 3).' '.substr($phoneDigits, 3, 3).' '.substr($phoneDigits, 6);
  }
  $fullPhone = trim($phoneCountry.' '.$formattedPhone);
  $_SESSION['book']['phone_country'] = $phoneCountry;
  $_SESSION['book']['phone_local']   = $formattedPhone ?: $phoneInput;
  $_SESSION['book']['phone']         = $fullPhone;

  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Geçerli bir e-posta adresi girin.';
  } else {
    $_SESSION['book']['email'] = $email;
  }

  $_SESSION['book']['birth'] = $birth;

  if (!verify_recaptcha($pdo, $_POST['g-recaptcha-response'] ?? '', request_ip())) {
    $errors[] = 'Robot doğrulaması doğrulanamadı.';
  }

  if (!$errors) {
    $clientIp = request_ip();
    $fingerprint = customer_fingerprint($fullName, $birth, $phoneCountry.$phoneDigits, $email);
    $duplicate = duplicate_appointment_exists($pdo, $clientIp, $fingerprint);
    if ($duplicate) {
      $_SESSION['book']['duplicate_lock'] = $duplicate;
      $_SESSION['book']['fingerprint'] = $fingerprint;
      $_SESSION['book']['client_ip'] = $clientIp;
      redirect('/?p=date');
    }
    $_SESSION['book']['fingerprint'] = $fingerprint;
    $_SESSION['book']['client_ip'] = $clientIp;
    unset($_SESSION['book']['duplicate_lock']);
    redirect('/?p=date');
  }
}
$prov = q($pdo, 'SELECT name,image FROM providers WHERE id=?', [$pid])->fetch();
$img  = ($prov && $prov['image'] && file_exists(__DIR__.'/uploads/providers/'.$prov['image']))
  ? '/uploads/providers/'.$prov['image']
  : 'https://placehold.co/96x96?text=+';
$g    = $_SESSION['book']['gender'] ?? '';
$phoneLocalDisplay = $phoneLocal ?: ($_SESSION['book']['phone_local'] ?? '');
$phoneOptions = [
  ['code' => '+90', 'label' => 'Türkiye', 'flag' => '🇹🇷'],
  ['code' => '+49', 'label' => 'Almanya', 'flag' => '🇩🇪'],
  ['code' => '+44', 'label' => 'Birleşik Krallık', 'flag' => '🇬🇧'],
  ['code' => '+33', 'label' => 'Fransa', 'flag' => '🇫🇷'],
  ['code' => '+1',  'label' => 'ABD', 'flag' => '🇺🇸'],
];
?>
<div class="p-6">
  <div class="flex items-center gap-4 mb-6">
    <img src="<?= $img ?>" class="w-16 h-16 rounded-xl object-cover border border-slate-200" loading="lazy" decoding="async" alt="<?= h($prov['name'] ?? '') ?>">
    <div>
      <div class="text-sm text-slate-500 uppercase tracking-wide"><?= h(t('book.provider')) ?></div>
      <div class="text-lg font-semibold text-slate-800"><?= h($prov['name'] ?? '') ?></div>
    </div>
  </div>
  <?php if ($errors): ?>
    <div class="md:col-span-2 mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3">
      <ul class="list-disc ml-4 space-y-1">
        <?php foreach ($errors as $error): ?>
          <li><?= h($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
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
      <input required type="date" name="birth" value="<?= h($_SESSION['book']['birth'] ?? '') ?>" max="<?= date('Y-m-d') ?>" class="mt-1 border rounded w-full p-2 focus:outline-none focus:ring" autocomplete="bday">
    </label>
    <label class="text-sm font-medium text-slate-600">
      <?= h(t('book.phone')) ?> *
      <div class="mt-1 flex items-stretch overflow-hidden rounded border focus-within:ring-2 focus-within:ring-sky-500">
        <select name="phone_country" class="bg-slate-100 px-3 py-2 text-sm focus:outline-none border-r">
          <?php foreach ($phoneOptions as $opt): ?>
            <option value="<?= h($opt['code']) ?>" <?= $opt['code'] === $phoneCountry ? 'selected' : '' ?>><?= h($opt['flag'].' '.$opt['code']) ?></option>
          <?php endforeach; ?>
        </select>
        <input required name="phone" value="<?= h($phoneLocalDisplay) ?>" class="flex-1 px-3 py-2 focus:outline-none" placeholder="501 234 5678" autocomplete="tel" pattern="\d{3} \d{3} \d{4}" data-phone-input>
      </div>
    </label>
    <label class="text-sm font-medium text-slate-600">
      <?= h(t('book.email')) ?>
      <input required type="email" name="email" value="<?= h($_SESSION['book']['email'] ?? '') ?>" class="mt-1 border rounded w-full p-2 focus:outline-none focus:ring" autocomplete="email">
    </label>
    <label class="text-sm font-medium text-slate-600 md:col-span-2">
      <?= h(t('book.note')) ?>
      <textarea name="note" class="mt-1 border rounded w-full p-2 focus:outline-none focus:ring" rows="3"><?= h($_SESSION['book']['note'] ?? '') ?></textarea>
    </label>
    <?php if (!empty($recaptcha['enabled']) && !empty($recaptcha['site_key'])): ?>
      <div class="md:col-span-2">
        <div class="g-recaptcha" data-sitekey="<?= h($recaptcha['site_key']) ?>"></div>
      </div>
    <?php endif; ?>
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
