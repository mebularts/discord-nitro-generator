<?php
declare(strict_types=1);

require_once __DIR__.'/../config/db.php';

$_settings_cache = $_settings_cache ?? [];

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
  global $_settings_cache;
  if (!isset($_settings_cache)) {
    $_settings_cache = [];
  }
  if (array_key_exists($key, $_settings_cache)) {
    return $_settings_cache[$key];
  }
  $stmt = $pdo->prepare('SELECT `value` FROM settings WHERE `key` = ?');
  $stmt->execute([$key]);
  $value = $stmt->fetchColumn();
  if ($value === false) {
    $_settings_cache[$key] = $default;
    return $default;
  }
  $decoded = json_decode((string)$value, true);
  $_settings_cache[$key] = $decoded === null && json_last_error() !== JSON_ERROR_NONE ? $value : $decoded;
  return $_settings_cache[$key];
}

function settings_set(PDO $pdo, string $key, $value): void {
  $stored = (is_array($value) || is_object($value)) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string)$value;
  $pdo->prepare('INSERT INTO settings(`key`,`value`) VALUES(?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)')->execute([$key, $stored]);
  global $_settings_cache;
  if (!isset($_settings_cache)) {
    $_settings_cache = [];
  }
  $_settings_cache[$key] = $value;
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
    'meta_keywords'=> '',
  ];
  $stored = settings_get($pdo, 'site', []);
  if (!is_array($stored)) $stored = [];
  return array_merge($defaults, $stored);
}

function contact_settings(PDO $pdo): array {
  $defaults = [
    'phone'     => '',
    'whatsapp'  => '',
    'instagram' => '',
    'facebook'  => '',
    'twitter'   => '',
    'tiktok'    => '',
    'linkedin'  => '',
  ];
  $stored = settings_get($pdo, 'contact', []);
  if (!is_array($stored)) $stored = [];
  $merged = array_merge($defaults, $stored);
  foreach ($merged as $key => $value) {
    if (!is_string($value)) {
      $merged[$key] = '';
    }
  }
  return $merged;
}

function recaptcha_settings(PDO $pdo): array {
  $defaults = [
    'enabled'    => false,
    'site_key'   => '',
    'secret_key' => '',
  ];
  $stored = settings_get($pdo, 'recaptcha', []);
  if (!is_array($stored)) $stored = [];
  $stored['enabled'] = !empty($stored['enabled']);
  $stored['site_key'] = trim((string)($stored['site_key'] ?? ''));
  $stored['secret_key'] = trim((string)($stored['secret_key'] ?? ''));
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

function smtp_settings(PDO $pdo): array {
  $defaults = [
    'enabled'   => false,
    'host'      => '',
    'port'      => 587,
    'encryption'=> 'tls',
    'username'  => '',
    'password'  => '',
    'from'      => 'noreply@example.com',
    'from_name' => 'Randevu Sistemi',
  ];
  $stored = settings_get($pdo, 'smtp', []);
  if (!is_array($stored)) $stored = [];
  $stored['enabled'] = !empty($stored['enabled']);
  $stored['port'] = isset($stored['port']) ? (int)$stored['port'] : $defaults['port'];
  return array_merge($defaults, $stored);
}

function sms_settings(PDO $pdo): array {
  $defaults = [
    'provider' => 'netgsm',
    'username' => '',
    'password' => '',
    'header'   => '',
  ];
  $stored = settings_get($pdo, 'sms', []);
  if (!is_array($stored)) $stored = [];
  return array_merge($defaults, $stored);
}

function notification_template_defaults(): array {
  return [
    'email' => [
      'appointment_customer' => [
        'subject' => 'Randevu Onayı #{appointment_id}',
        'body'    => '<p>Merhaba {customer_name},</p><p>{provider_name} ile {appointment_date} tarihinde saat {appointment_time} için randevunuz oluşturuldu.</p>',
      ],
      'appointment_provider' => [
        'subject' => 'Yeni Randevu #{appointment_id}',
        'body'    => '<p>Merhaba {provider_name},</p><p>{customer_name} tarafından {appointment_date} {appointment_time} tarihinde randevu alındı.</p>',
      ],
      'bulk_default' => [
        'subject' => 'Duyuru',
        'body'    => '<p>{message}</p>',
      ],
    ],
    'sms' => [
      'appointment_customer' => 'Randevunuz {appointment_date} {appointment_time} tarihinde onaylandı.',
      'appointment_provider' => '{customer_name}, {appointment_date} {appointment_time} için yeni randevu aldı.',
      'bulk_default' => '{message}',
    ],
  ];
}

function notification_templates(PDO $pdo): array {
  $defaults = notification_template_defaults();
  $stored = settings_get($pdo, 'notification_templates', []);
  if (!is_array($stored)) $stored = [];
  return array_replace_recursive($defaults, $stored);
}

function notification_template(PDO $pdo, string $channel, string $key): array|string {
  $templates = notification_templates($pdo);
  return $templates[$channel][$key] ?? ($channel === 'email' ? ['subject' => '', 'body' => ''] : '');
}

function format_notification(string $content, array $context): string {
  $replacements = [];
  foreach ($context as $key => $value) {
    $replacements['{'.$key.'}'] = (string)$value;
  }
  return strtr($content, $replacements);
}

function customer_history(PDO $pdo, string $customerKey): array {
  $stmt = q(
    $pdo,
    'SELECT a.*, p.name AS provider_name FROM appointments a LEFT JOIN providers p ON p.id = a.provider_id WHERE (COALESCE(NULLIF(a.email, ""), a.phone) = ?) ORDER BY a.app_date DESC, a.app_time DESC, a.id DESC LIMIT 200',
    [$customerKey]
  );
  return $stmt->fetchAll();
}

function request_ip(): string {
  $candidates = [
    $_SERVER['HTTP_CF_CONNECTING_IP'] ?? null,
    $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
    $_SERVER['REMOTE_ADDR'] ?? null,
  ];
  foreach ($candidates as $candidate) {
    if (!$candidate) continue;
    $ipList = explode(',', $candidate);
    foreach ($ipList as $ip) {
      $ip = trim($ip);
      if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
        return $ip;
      }
    }
  }
  return '0.0.0.0';
}

function normalize_phone(string $phone): string {
  return preg_replace('/[^0-9+]/', '', $phone);
}

function customer_fingerprint(string $fullName, string $birth, string $phone, string $email): string {
  $parts = [
    mb_strtolower(trim($fullName), 'UTF-8'),
    preg_replace('/[^0-9]/', '', $birth),
    preg_replace('/[^0-9]/', '', $phone),
    mb_strtolower(trim($email), 'UTF-8'),
  ];
  return sha1(implode('|', $parts));
}

function duplicate_appointment_exists(PDO $pdo, string $ip, string $fingerprint): ?array {
  if (!$ip || !$fingerprint) {
    return null;
  }
  $stmt = q(
    $pdo,
    'SELECT a.id, a.app_date, a.app_time, p.name AS provider_name FROM appointments a LEFT JOIN providers p ON p.id = a.provider_id WHERE a.client_ip = ? AND a.customer_fingerprint = ? AND a.status <> "cancelled" AND a.app_date >= CURDATE() ORDER BY a.app_date ASC, a.app_time ASC, a.id ASC LIMIT 1',
    [$ip, $fingerprint]
  );
  $row = $stmt->fetch();
  return $row ?: null;
}

function verify_recaptcha(PDO $pdo, string $token, ?string $ip = null): bool {
  $settings = recaptcha_settings($pdo);
  if (empty($settings['enabled']) || empty($settings['secret_key'])) {
    return true;
  }
  $token = trim($token);
  if ($token === '') {
    return false;
  }
  $ip = $ip ?: request_ip();
  $payload = http_build_query([
    'secret'   => $settings['secret_key'],
    'response' => $token,
    'remoteip' => $ip,
  ]);
  $context = stream_context_create([
    'http' => [
      'method'  => 'POST',
      'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
      'content' => $payload,
      'timeout' => 10,
    ],
  ]);
  $response = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
  if ($response === false) {
    return false;
  }
  $data = json_decode($response, true);
  return !empty($data['success']);
}

function latest_customer_appointment(PDO $pdo, string $customerKey): ?array {
  $stmt = q(
    $pdo,
    'SELECT a.*, p.name AS provider_name FROM appointments a LEFT JOIN providers p ON p.id = a.provider_id WHERE (COALESCE(NULLIF(a.email, ""), a.phone) = ?) ORDER BY a.app_date DESC, a.id DESC LIMIT 1',
    [$customerKey]
  );
  $row = $stmt->fetch();
  return $row ?: null;
}

function provider_default_duration(PDO $pdo, int $providerId): int {
  $duration = (int)q($pdo, 'SELECT default_duration FROM providers WHERE id=?', [$providerId])->fetchColumn();
  return $duration > 0 ? $duration : booking_settings($pdo)['slot_minutes'];
}

function provider_availability_for_day(PDO $pdo, int $providerId, int $weekday): ?array {
  static $cache = [];
  $key = $providerId.'-'.$weekday;
  if (array_key_exists($key, $cache)) {
    return $cache[$key];
  }
  $row = q($pdo, 'SELECT * FROM provider_availability WHERE provider_id=? AND weekday=? LIMIT 1', [$providerId, $weekday])->fetch();
  $cache[$key] = $row ?: null;
  return $cache[$key];
}

function provider_timeoffs_for_date(PDO $pdo, int $providerId, string $date): array {
  static $cache = [];
  $monthKey = $providerId.'-'.date('Y-m', strtotime($date));
  if (!isset($cache[$monthKey])) {
    $start = date('Y-m-01', strtotime($date));
    $end   = date('Y-m-t', strtotime($date));
    $rows = q(
      $pdo,
      'SELECT date,start_time,end_time,is_recurring,weekday FROM provider_timeoffs WHERE provider_id=? AND (date BETWEEN ? AND ? OR is_recurring=1)',
      [$providerId, $start, $end]
    )->fetchAll();
    $indexed = ['recurring' => []];
    foreach ($rows as $row) {
      if (!empty($row['is_recurring'])) {
        $indexed['recurring'][] = $row;
      } elseif (!empty($row['date'])) {
        $indexed[$row['date']][] = $row;
      }
    }
    $cache[$monthKey] = $indexed;
  }
  $day = $cache[$monthKey][$date] ?? [];
  $weekday = (int)date('N', strtotime($date));
  foreach ($cache[$monthKey]['recurring'] ?? [] as $row) {
    if ((int)$row['weekday'] === $weekday) {
      $day[] = $row;
    }
  }
  return $day;
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

  static $busyCache = [];
  $monthKey = $providerId.'-'.date('Y-m', strtotime($date));
  if (!isset($busyCache[$monthKey])) {
    $start = date('Y-m-01', strtotime($date));
    $end   = date('Y-m-t', strtotime($date));
    $rows = q(
      $pdo,
      'SELECT app_date, app_time FROM appointments WHERE provider_id=? AND app_date BETWEEN ? AND ? AND status <> "cancelled"',
      [$providerId, $start, $end]
    )->fetchAll();
    $map = [];
    foreach ($rows as $row) {
      $map[$row['app_date']][] = $row['app_time'];
    }
    $busyCache[$monthKey] = $map;
  }
  $busy = $busyCache[$monthKey][$date] ?? [];
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
    'notifications'=> 'Bildirimler',
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
