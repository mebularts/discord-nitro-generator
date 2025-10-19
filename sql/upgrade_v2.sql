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
  CONSTRAINT fk_admin_role_upgrade FOREIGN KEY (role_id) REFERENCES admin_roles(id) ON DELETE SET NULL
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
  CONSTRAINT fk_translation_language_upgrade FOREIGN KEY (lang_id) REFERENCES languages(id) ON DELETE CASCADE
);

ALTER TABLE providers
  ADD COLUMN IF NOT EXISTS bio TEXT NULL,
  ADD COLUMN IF NOT EXISTS primary_color VARCHAR(20) DEFAULT '#0284c7',
  ADD COLUMN IF NOT EXISTS secondary_color VARCHAR(20) DEFAULT '#4f46e5',
  ADD COLUMN IF NOT EXISTS default_duration INT NOT NULL DEFAULT 30;

ALTER TABLE providers ADD COLUMN IF NOT EXISTS email VARCHAR(120) NULL;
ALTER TABLE providers ADD COLUMN IF NOT EXISTS phone VARCHAR(40) NULL;

CREATE TABLE IF NOT EXISTS provider_availability (
  id INT AUTO_INCREMENT PRIMARY KEY,
  provider_id INT NOT NULL,
  weekday TINYINT NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  slot_minutes INT NOT NULL DEFAULT 30,
  UNIQUE KEY uniq_provider_day (provider_id, weekday),
  CONSTRAINT fk_availability_provider_upgrade FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS provider_timeoffs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  provider_id INT NOT NULL,
  date DATE NULL,
  weekday TINYINT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  is_recurring TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT fk_timeoff_provider_upgrade FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE
);

ALTER TABLE appointments
  ADD COLUMN IF NOT EXISTS duration_minutes INT NOT NULL DEFAULT 30,
  ADD COLUMN IF NOT EXISTS category VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS lang_code VARCHAR(10) NULL,
  ADD COLUMN IF NOT EXISTS admin_note TEXT NULL,
  ADD COLUMN IF NOT EXISTS client_ip VARCHAR(45) NULL,
  ADD COLUMN IF NOT EXISTS customer_fingerprint CHAR(40) NULL;

CREATE INDEX IF NOT EXISTS idx_app_fingerprint ON appointments (client_ip, customer_fingerprint);

INSERT IGNORE INTO admin_roles (id, name, permissions) VALUES (1, 'Tam Yetki', JSON_ARRAY('dashboard','appointments','customers','providers','availability','notifications','settings','languages','translations','users'));
INSERT IGNORE INTO admin_users (username, full_name, password, role_id, is_active, is_super) VALUES ('admin', 'Yönetici', '$2y$10$wT7aCbtm1qFq6l5t6SgPTO1w1b8r3qPZ6e3dA2pS7Zp2q9D0eN0XG', NULL, 1, 1);
INSERT IGNORE INTO languages (id, code, name, is_default) VALUES (1, 'tr', 'Türkçe', 1);

INSERT IGNORE INTO settings (`key`,`value`) VALUES
 ('site', '{"title":"Randevu Sistemi","description":"Modern, hızlı ve mobil uyumlu.","footer":"© 2025 v2.0","meta_keywords":"randevu, danışmanlık"}'),
 ('contact', '{"phone":"","whatsapp":"","instagram":"","facebook":"","twitter":"","tiktok":"","linkedin":""}'),
 ('theme', '{"primary":"#0284c7","secondary":"#4f46e5","surface":"#ffffff","background":"#f8fafc","text":"#0f172a"}'),
 ('booking', '{"slot_minutes":30,"day_start":"09:00","day_end":"18:00","lead_days":0,"max_days":60,"allow_weekend":0}'),
 ('custom_assets', '{"css":"","js":""}'),
 ('recaptcha', '{"enabled":0,"site_key":"","secret_key":""}'),
 ('smtp', '{"enabled":0,"host":"","port":587,"encryption":"tls","username":"","password":"","from":"noreply@example.com","from_name":"Randevu Sistemi"}'),
 ('sms', '{"provider":"netgsm","username":"","password":"","header":""}'),
 ('notification_templates', '{"email":{"appointment_customer":{"subject":"Randevu Onayı #{appointment_id}","body":"<p>Merhaba {customer_name},</p><p>{provider_name} ile {appointment_date} tarihinde saat {appointment_time} için randevunuz oluşturuldu.</p>"},"appointment_provider":{"subject":"Yeni Randevu #{appointment_id}","body":"<p>Merhaba {provider_name},</p><p>{customer_name} tarafından {appointment_date} {appointment_time} tarihinde randevu alındı.</p>"},"bulk_default":{"subject":"Duyuru","body":"<p>{message}</p>"}},"sms":{"appointment_customer":"Randevunuz {appointment_date} {appointment_time} tarihinde onaylandı.","appointment_provider":"{customer_name}, {appointment_date} {appointment_time} için yeni randevu aldı.","bulk_default":"{message}"}}');
