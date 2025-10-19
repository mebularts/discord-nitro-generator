-- Güncellenmiş Randevu Sistemi Şeması
CREATE TABLE IF NOT EXISTS admin_roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  permissions JSON NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  full_name VARCHAR(120) NULL,
  password VARCHAR(255) NOT NULL,
  role_id INT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  is_super TINYINT(1) NOT NULL DEFAULT 0,
  last_login_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_admin_role FOREIGN KEY (role_id) REFERENCES admin_roles(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS languages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(10) NOT NULL UNIQUE,
  name VARCHAR(60) NOT NULL,
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS translations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lang_id INT NOT NULL,
  `key` VARCHAR(150) NOT NULL,
  `value` TEXT NOT NULL,
  UNIQUE KEY uniq_lang_key (lang_id, `key`),
  CONSTRAINT fk_translation_language FOREIGN KEY (lang_id) REFERENCES languages(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS providers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  bio TEXT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  sort INT NOT NULL DEFAULT 0,
  image VARCHAR(255) NULL,
  primary_color VARCHAR(20) DEFAULT '#0284c7',
  secondary_color VARCHAR(20) DEFAULT '#4f46e5',
  default_duration INT NOT NULL DEFAULT 30
);

CREATE TABLE IF NOT EXISTS provider_availability (
  id INT AUTO_INCREMENT PRIMARY KEY,
  provider_id INT NOT NULL,
  weekday TINYINT NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  slot_minutes INT NOT NULL DEFAULT 30,
  UNIQUE KEY uniq_provider_day (provider_id, weekday),
  CONSTRAINT fk_availability_provider FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS provider_timeoffs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  provider_id INT NOT NULL,
  date DATE NULL,
  weekday TINYINT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  is_recurring TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT fk_timeoff_provider FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  provider_id INT NOT NULL,
  full_name VARCHAR(120) NOT NULL,
  gender VARCHAR(20) NULL,
  birth VARCHAR(20) NULL,
  phone VARCHAR(40) NOT NULL,
  email VARCHAR(120) NULL,
  note TEXT NULL,
  app_date DATE NOT NULL,
  app_time VARCHAR(5) NOT NULL,
  duration_minutes INT NOT NULL DEFAULT 30,
  status VARCHAR(20) NOT NULL DEFAULT 'new',
  category VARCHAR(50) NULL,
  lang_code VARCHAR(10) NULL,
  admin_note TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  reminder_sent TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT fk_appt_provider FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE,
  INDEX idx_provider_date (provider_id, app_date)
);

CREATE TABLE IF NOT EXISTS settings (
  `key` VARCHAR(100) PRIMARY KEY,
  `value` TEXT NOT NULL
);

INSERT INTO admin_roles (id, name, permissions) VALUES
(1, 'Tam Yetki', JSON_ARRAY('dashboard','appointments','customers','providers','availability','settings','languages','translations','users'))
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO admin_users (id, username, full_name, password, role_id, is_active, is_super) VALUES
(1, 'admin', 'Yönetici', '$2y$10$wT7aCbtm1qFq6l5t6SgPTO1w1b8r3qPZ6e3dA2pS7Zp2q9D0eN0XG', NULL, 1, 1)
ON DUPLICATE KEY UPDATE full_name=VALUES(full_name);

INSERT INTO languages (id, code, name, is_default) VALUES
(1, 'tr', 'Türkçe', 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO providers (id, name, bio, active, sort, image, primary_color, secondary_color, default_duration) VALUES
(1, 'Ahmet Demircan', 'Genel danışman', 1, 1, NULL, '#0284c7', '#4f46e5', 30),
(2, 'Hafzullah YILDIRIM', 'Kıdemli uzman', 1, 2, NULL, '#0284c7', '#4f46e5', 30),
(3, 'Mustafa DÜNDAR', 'Uzman danışman', 1, 3, NULL, '#0284c7', '#4f46e5', 30)
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO provider_availability (provider_id, weekday, start_time, end_time, slot_minutes) VALUES
(1,1,'09:00','18:00',30),(1,2,'09:00','18:00',30),(1,3,'09:00','18:00',30),(1,4,'09:00','18:00',30),(1,5,'09:00','18:00',30),
(2,1,'09:00','18:00',30),(2,2,'09:00','18:00',30),(2,3,'09:00','18:00',30),(2,4,'09:00','18:00',30),(2,5,'09:00','18:00',30),
(3,1,'09:00','18:00',30),(3,2,'09:00','18:00',30),(3,3,'09:00','18:00',30),(3,4,'09:00','18:00',30),(3,5,'09:00','18:00',30)
ON DUPLICATE KEY UPDATE start_time=VALUES(start_time);

INSERT INTO settings (`key`,`value`) VALUES
('site', '{"title":"Randevu Sistemi","description":"Modern, hızlı ve mobil uyumlu.","footer":"© 2025 v2.0"}'),
('theme', '{"primary":"#0284c7","secondary":"#4f46e5","surface":"#ffffff","background":"#f8fafc","text":"#0f172a"}'),
('booking', '{"slot_minutes":30,"day_start":"09:00","day_end":"18:00","lead_days":0,"max_days":60,"allow_weekend":0}'),
('custom_assets', '{"css":"","js":""}')
ON DUPLICATE KEY UPDATE `value`=VALUES(`value`);

INSERT INTO appointments (id, provider_id, full_name, gender, birth, phone, email, note, app_date, app_time, duration_minutes, status, category, lang_code, created_at, reminder_sent) VALUES
(1, 1, 'Mehmet Bulat', 'Erkek', '1990-05-10', '+905000000000', 'admin@admin.com', 'Test notu', '2025-10-14', '13:00', 30, 'new', NULL, 'tr', NOW(), 0)
ON DUPLICATE KEY UPDATE full_name=VALUES(full_name);

INSERT INTO translations (lang_id, `key`, `value`) VALUES
(1,'title','Randevu Sistemi'),
(1,'subtitle','Modern, hızlı ve mobil uyumlu.'),
(1,'home.choose_provider','Randevu almak için bir personel seçin'),
(1,'home.no_provider','Henüz personel tanımlı değil.'),
(1,'home.book_button','Randevu al'),
(1,'language.switch','Dil'),
(1,'btn.next','Devam et'),
(1,'btn.prev','Geri dön'),
(1,'book.info','Genel bilgiler'),
(1,'book.date','Randevu tarihi'),
(1,'book.time','Randevu saati'),
(1,'book.provider','Randevu veren'),
(1,'book.full_name','Ad Soyad'),
(1,'book.gender','Cinsiyet'),
(1,'book.gender.female','Kadın'),
(1,'book.gender.male','Erkek'),
(1,'book.birth','Doğum tarihi'),
(1,'book.phone','Telefon'),
(1,'book.email','E-posta'),
(1,'book.note','Notunuz'),
(1,'time.morning','Öğleden önce'),
(1,'time.afternoon','Öğleden sonra'),
(1,'confirm.title','Randevunuzu onaylayın'),
(1,'confirm.info','Bilgileriniz'),
(1,'confirm.provider','Randevu veren'),
(1,'confirm.date','Randevu tarihi'),
(1,'confirm.time','Randevu saati'),
(1,'confirm.submit','Randevuyu onayla'),
(1,'success.title','Randevunuz Oluşturuldu!'),
(1,'success.text','En kısa sürede sizinle iletişime geçilecektir.'),
(1,'progress.book','Genel bilgiler'),
(1,'progress.date','Randevu tarihi'),
(1,'progress.time','Randevu saati'),
(1,'progress.confirm','Onay'),
(1,'progress.completed','Tamamlandı'),
(1,'progress.here','Buradasınız'),
(1,'date.available','Müsait'),
(1,'date.unavailable','Müsait değil'),
(1,'footer.text','© 2025 v2.0'),
(1,'done.download_ics','Takvime ekle (.ics)'),
(1,'done.print','Yazdır / PDF'),
(1,'done.new_appointment','Yeni randevu oluştur'),
(1,'done.no_appointment','Gösterilecek randevu bulunamadı.')
ON DUPLICATE KEY UPDATE `value`=VALUES(`value`);
