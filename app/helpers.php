<?php
declare(strict_types=1);

require_once __DIR__.'/../config/db.php';

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function redirect(string $u){ header('Location: '.$u); exit; }
function is_post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'; }

function csrf_token(): string {
  if (empty($_SESSION['_csrf'])) {
    $_SESSION['_csrf'] = bin2hex(random_bytes(16));
  }
  return (string)$_SESSION['_csrf'];
}

function csrf_field(): string { return '<input type="hidden" name="_csrf" value="'.h(csrf_token()).'">'; }

function csrf_check(): void {
  if (($_POST['_csrf'] ?? '') !== ($_SESSION['_csrf'] ?? '')) {
    http_response_code(419);
    exit('CSRF token mismatch');
  }
}

function set_flash(string $key, $value): void { $_SESSION['_flash'][$key] = $value; }

function get_flash(string $key){ $value = $_SESSION['_flash'][$key] ?? null; unset($_SESSION['_flash'][$key]); return $value; }

function q(PDO $pdo, string $sql, array $args = []){ $stmt = $pdo->prepare($sql); $stmt->execute($args); return $stmt; }

function settings_get(PDO $pdo, string $key, $default = null){
  $stmt = $pdo->prepare('SELECT `value` FROM settings WHERE `key` = ?');
  $stmt->execute([$key]);
  $value = $stmt->fetchColumn();
  if ($value === false) return $default;
  $decoded = json_decode((string)$value, true);
  return $decoded === null && json_last_error() !== JSON_ERROR_NONE ? $value : $decoded;
}

function settings_set(PDO $pdo, string $key, $value): void {
  $stored = (is_array($value) || is_object($value)) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string)$value;
  $pdo->prepare('INSERT INTO settings(`key`,`value`) VALUES(?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)')->execute([$key, $stored]);
}

function translation_defaults(): array {
  return [
    'title'                 => 'Randevu Sistemi',
    'subtitle'              => 'Modern, hızlı ve mobil uyumlu.',
    'home.choose_provider'  => 'Randevu almak için bir personel seçin',
    'home.no_provider'      => 'Henüz personel tanımlı değil.',
    'home.book_button'      => 'Randevu al',
    'language.switch'       => 'Dil',
    'btn.next'              => 'Devam et',
    'btn.prev'              => 'Geri dön',
    'book.info'             => 'Genel bilgiler',
    'book.date'             => 'Randevu tarihi',
    'book.time'             => 'Randevu saati',
    'book.provider'         => 'Randevu veren',
    'book.full_name'        => 'Ad Soyad',
    'book.gender'           => 'Cinsiyet',
    'book.gender.female'    => 'Kadın',
    'book.gender.male'      => 'Erkek',
    'book.birth'            => 'Doğum tarihi',
    'book.phone'            => 'Telefon',
    'book.email'            => 'E-posta',
    'book.note'             => 'Notunuz',
    'time.morning'          => 'Öğleden önce',
    'time.afternoon'        => 'Öğleden sonra',
    'confirm.title'         => 'Randevunuzu onaylayın',
    'confirm.provider'      => 'Randevu veren',
    'confirm.date'          => 'Randevu tarihi',
    'confirm.time'          => 'Randevu saati',
    'confirm.info'          => 'Bilgileriniz',
    'confirm.submit'        => 'Randevuyu onayla',
    'success.title'         => 'Randevunuz Oluşturuldu!',
    'success.text'          => 'En kısa sürede sizinle iletişime geçilecektir.',
    'progress.book'         => 'Genel bilgiler',
    'progress.date'         => 'Randevu tarihi',
    'progress.time'         => 'Randevu saati',
    'progress.confirm'      => 'Onay',
    'progress.completed'    => 'Tamamlandı',
    'progress.here'         => 'Buradasınız',
    'date.available'        => 'Müsait',
    'date.unavailable'      => 'Müsait değil',
    'footer.text'           => '© 2025 v2.0',
    'done.download_ics'     => 'Takvime ekle (.ics)',
    'done.print'            => 'Yazdır / PDF',
    'done.new_appointment'  => 'Yeni randevu oluştur',
    'done.no_appointment'   => 'Gösterilecek randevu bulunamadı.',
  ];
}

function ensure_language_bootstrap(PDO $pdo): void {
  static $bootstrapped = false;
  if ($bootstrapped) return;

  try {
    $pdo->query('SELECT 1 FROM languages LIMIT 1');
  } catch (Throwable $e) {
    // Table does not exist yet (during installation)
    return;
  }

  $defaults = translation_defaults();

  $langCount = (int)$pdo->query('SELECT COUNT(*) FROM languages')->fetchColumn();
  if ($langCount === 0) {
    $pdo->prepare('INSERT INTO languages(code,name,is_default) VALUES (?,?,1)')->execute(['tr','Türkçe',1]);
  }

  $defaultCode = default_language_code($pdo);
  $langIdStmt = $pdo->prepare('SELECT id FROM languages WHERE code = ? LIMIT 1');
  $langIdStmt->execute([$defaultCode]);
  $langId = (int)$langIdStmt->fetchColumn();

  if ($langId > 0) {
    $insert = $pdo->prepare('INSERT IGNORE INTO translations(lang_id,`key`,`value`) VALUES(?,?,?)');
    foreach ($defaults as $key => $value) {
      $insert->execute([$langId, $key, $value]);
    }
  }

  $bootstrapped = true;
}

function available_languages(PDO $pdo): array {
  ensure_language_bootstrap($pdo);
  try {
    $rows = q($pdo, 'SELECT * FROM languages ORDER BY name')->fetchAll();
  } catch (Throwable $e) {
    return [];
  }
  $out = [];
  foreach ($rows as $row) {
    $out[$row['code']] = $row;
  }
  return $out;
}

function default_language_code(PDO $pdo): string {
  try {
    $row = q($pdo, 'SELECT code FROM languages WHERE is_default=1 ORDER BY id LIMIT 1')->fetchColumn();
    if ($row) return (string)$row;
    $row = q($pdo, 'SELECT code FROM languages ORDER BY id LIMIT 1')->fetchColumn();
    if ($row) return (string)$row;
  } catch (Throwable $e) {
    return 'tr';
  }
  return 'tr';
}

function set_current_language(PDO $pdo, string $code): void {
  $langs = available_languages($pdo);
  if (isset($langs[$code])) {
    $_SESSION['_lang'] = $code;
  }
}

function current_language_code(PDO $pdo): string {
  $langs = available_languages($pdo);
  $code = $_SESSION['_lang'] ?? null;
  if ($code && isset($langs[$code])) return $code;
  return default_language_code($pdo);
}

function t(string $key, ?string $lang = null): string {
  global $pdo;
  ensure_language_bootstrap($pdo);
  if (!isset($GLOBALS['_translation_cache']) || !is_array($GLOBALS['_translation_cache'])) {
    $GLOBALS['_translation_cache'] = [];
  }
  $cache = &$GLOBALS['_translation_cache'];

  $lang = $lang ?? current_language_code($pdo);
  if (!isset($cache[$lang])) {
    try {
      $stmt = q($pdo, 'SELECT `key`,`value` FROM translations t JOIN languages l ON l.id=t.lang_id WHERE l.code=?', [$lang]);
      $cache[$lang] = [];
      foreach ($stmt as $row) {
        $cache[$lang][$row['key']] = $row['value'];
      }
    } catch (Throwable $e) {
      $cache[$lang] = [];
    }
  }

  if (isset($cache[$lang][$key])) return (string)$cache[$lang][$key];

  $default = default_language_code($pdo);
  if ($default !== $lang) {
    if (!isset($cache[$default])) {
      $stmt = q($pdo, 'SELECT `key`,`value` FROM translations t JOIN languages l ON l.id=t.lang_id WHERE l.code=?', [$default]);
      $cache[$default] = [];
      foreach ($stmt as $row) {
        $cache[$default][$row['key']] = $row['value'];
      }
    }
    if (isset($cache[$default][$key])) return (string)$cache[$default][$key];
  }

  $defaults = translation_defaults();
  return $defaults[$key] ?? $key;
}

function clear_translation_cache(): void {
  $GLOBALS['_translation_cache'] = [];
}

function theme_settings(PDO $pdo): array {
  $defaults = [
    'primary'   => '#0284c7',
    'secondary' => '#4f46e5',
    'surface'   => '#ffffff',
    'background'=> '#f8fafc',
    'text'      => '#0f172a',
  ];
  $stored = settings_get($pdo, 'theme', []);
  if (!is_array($stored)) $stored = [];
  return array_merge($defaults, $stored);
}

function site_settings(PDO $pdo): array {
  $defaults = [
    'title'       => translation_defaults()['title'],
    'description' => translation_defaults()['subtitle'],
    'footer'      => translation_defaults()['footer.text'],
  ];
  $stored = settings_get($pdo, 'site', []);
  if (!is_array($stored)) $stored = [];
  return array_merge($defaults, $stored);
}

function custom_assets(PDO $pdo): array {
  $defaults = ['css' => '', 'js' => ''];
  $stored = settings_get($pdo, 'custom_assets', []);
  if (!is_array($stored)) $stored = [];
  return array_merge($defaults, $stored);
}

function booking_settings(PDO $pdo): array {
  $defaults = [
    'slot_minutes'  => 30,
    'day_start'     => '09:00',
    'day_end'       => '18:00',
    'lead_days'     => 0,
    'max_days'      => 60,
    'allow_weekend' => 0,
  ];
  $stored = settings_get($pdo, 'booking', []);
  if (!is_array($stored)) $stored = [];
  $merged = array_merge($defaults, $stored);
  $merged['slot_minutes'] = max(5, (int)$merged['slot_minutes']);
  $merged['lead_days'] = max(0, (int)$merged['lead_days']);
  $merged['max_days'] = max($merged['lead_days'], (int)$merged['max_days']);
  $merged['allow_weekend'] = (int)$merged['allow_weekend'] ? 1 : 0;
  return $merged;
}

function provider_default_duration(PDO $pdo, int $providerId): int {
  $duration = (int)q($pdo, 'SELECT default_duration FROM providers WHERE id=?', [$providerId])->fetchColumn();
  return $duration > 0 ? $duration : booking_settings($pdo)['slot_minutes'];
}

function provider_availability_for_day(PDO $pdo, int $providerId, int $weekday): ?array {
  $row = q($pdo, 'SELECT * FROM provider_availability WHERE provider_id=? AND weekday=? LIMIT 1', [$providerId, $weekday])->fetch();
  return $row ?: null;
}

function provider_timeoffs_for_date(PDO $pdo, int $providerId, string $date): array {
  return q($pdo, 'SELECT start_time,end_time FROM provider_timeoffs WHERE provider_id=? AND (date=? OR (is_recurring=1 AND weekday=?))', [$providerId, $date, (int)date('N', strtotime($date))])->fetchAll();
}

function provider_slots_for_date(PDO $pdo, int $providerId, string $date): array {
  $weekday = (int)date('N', strtotime($date));
  if (!$weekday) return [];

  $availability = provider_availability_for_day($pdo, $providerId, $weekday);
  if (!$availability) return [];

  $start = strtotime($availability['start_time']);
  $end   = strtotime($availability['end_time']);
  $step  = max(5, (int)$availability['slot_minutes']);

  $slots = [];
  while ($start < $end) {
    $slots[] = date('H:i', $start);
    $start += $step * 60;
  }

  $timeoffs = provider_timeoffs_for_date($pdo, $providerId, $date);
  $slots = array_values(array_filter($slots, function ($slot) use ($timeoffs) {
    $ts = strtotime($slot);
    foreach ($timeoffs as $off) {
      if (!$off['start_time'] || !$off['end_time']) continue;
      $start = strtotime($off['start_time']);
      $end = strtotime($off['end_time']);
      if ($ts >= $start && $ts < $end) return false;
    }
    return true;
  }));

  $busy = q($pdo, 'SELECT app_time FROM appointments WHERE provider_id=? AND app_date=? AND status <> "cancelled"', [$providerId, $date])->fetchAll(PDO::FETCH_COLUMN);
  if ($busy) {
    $slots = array_values(array_diff($slots, $busy));
  }

  return $slots;
}

function must_login(array $permissions = []): void {
  if (empty($_SESSION['admin'])) redirect('/admin/login.php');
  if ($permissions) {
    foreach ($permissions as $perm) {
      if (!has_permission($perm)) {
        http_response_code(403);
        exit('Bu işlem için yetkiniz yok.');
      }
    }
  }
}

function current_admin(): ?array { return $_SESSION['admin'] ?? null; }

function has_permission(string $permission): bool {
  $admin = current_admin();
  if (!$admin) return false;
  if (!empty($admin['is_super'])) return true;
  $perms = $admin['permissions'] ?? [];
  return in_array($permission, $perms, true);
}

function permission_labels(): array {
  return [
    'dashboard'    => 'Gösterge Paneli',
    'appointments' => 'Randevular',
    'customers'    => 'Müşteriler',
    'providers'    => 'Randevu Verenler',
    'availability' => 'Müsaitlik Yönetimi',
    'settings'     => 'Site Ayarları',
    'languages'    => 'Dil Yönetimi',
    'translations' => 'Çeviriler',
    'users'        => 'Kullanıcı Yönetimi',
  ];
}

function appointment_statuses(): array {
  return [
    'new'        => 'Yeni',
    'confirmed'  => 'Onaylandı',
    'approved'   => 'Onaylandı',
    'completed'  => 'Tamamlandı',
    'cancelled'  => 'İptal edildi',
    'no_show'    => 'Gelmedi',
    'pending'    => 'Beklemede',
  ];
}

function format_month_year(DateTime $dt, string $locale = 'tr_TR'): string {
  if (class_exists('IntlDateFormatter')) {
    $fmt = new IntlDateFormatter($locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, $dt->getTimezone()->getName(), IntlDateFormatter::GREGORIAN, 'LLLL yyyy');
    $s = $fmt->format($dt);
    if ($s !== false) return $s;
  }
  static $ay = ['01'=>'Ocak','02'=>'Şubat','03'=>'Mart','04'=>'Nisan','05'=>'Mayıs','06'=>'Haziran','07'=>'Temmuz','08'=>'Ağustos','09'=>'Eylül','10'=>'Ekim','11'=>'Kasım','12'=>'Aralık'];
  $m = $dt->format('m');
  return ($ay[$m] ?? $dt->format('F')).' '.$dt->format('Y');
}
