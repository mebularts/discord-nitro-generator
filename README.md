# SolveClone

SolveClone; PHP 8+ ve MySQL/MariaDB üzerinde çalışan modern bir bulmaca & soru-cevap platformu ile yönetim paneli sağlar. Proje PWA desteği, profil kişiselleştirme, soru-cevap akışları ve minimum bağımlılıklı bir mimari sunar.

## Özellikler

- **Kurulum sihirbazı:** `/admin/setup.php` üzerinden `install/install.sql` şemasını çalıştırır, ilk admin hesabını oluşturur.
- **App\ yardımcıları:** `App\db()`, `App\h()`, `App\csrf_token()`, `App\csrf_check()`, `App\asset_url()` fonksiyonları ile tekil ve güvenli altyapı.
- **Profil deneyimi:** Bio, renk, avatar, sosyal link yönetimi; ana sayfa görünürlüğü kontrolü ve 40 günde bir kullanıcı adı değiştirme.
- **Soru & cevap akışı:** Kullanıcılar birbirine anonim veya görünür sorular gönderebilir, cevaplarını herkese açık/gizli işaretleyebilir.
- **Bildirimler:** Yeni soru ve cevap aktiviteleri için bildirim kaydı ve okundu yönetimi.
- **Modern arayüz:** Bootstrap 5.3, koyu/açık tema, erişilebilirlik odaklı bileşenler ve boş durum tasarımları.
- **PWA:** Manifest + servis worker ile çevrimdışı destek, `/admin` yolları hiçbir koşulda önbelleğe alınmaz.
- **Yönetim paneli:** Kullanıcı, soru, cevap ve bildirim listeleri; ayarlar ekranı ile site adı ve tema kontrolü.

## Dizin Yapısı

```
/app
  bootstrap.php
  db.php
  helpers.php
  auth.php
  notifications.php
  /models
/public
  index.php
  offline.html
  manifest.webmanifest
  sw.js
  /assets/css/app.css
  /assets/js/app.js
  /profiles/avatars
  /admin (login, dashboard, setup, listeler)
/install/install.sql
```

## Gereksinimler

- PHP 8.1+
- MySQL veya MariaDB
- PDO eklentisi

## Kurulum

1. Depoyu sunucunuza taşıyın ve web kökünü `public/` klasörü olarak yapılandırın.
2. Veritabanı bilgileri için ortam değişkenlerini (`DB_DSN`, `DB_USER`, `DB_PASS`) belirleyin.
3. Tarayıcıdan `/admin/setup.php` adresine gidin. Kurulum sihirbazı tabloları oluşturup ilk yönetici hesabını açacaktır.
4. Yönetim girişini `/admin/login.php` üzerinden yapın.
5. Kullanıcılar `/register.php` ile kayıt olup, profil ayarlarını `/settings.php` sayfasından yönetebilir.

## PWA

`public/sw.js` servis worker dosyası yalnızca GET ve aynı origin isteklerini önbelleğe alır. `/admin` yolları hariç tutulur, gezinme istekleri için network-first stratejisi uygulanır ve çevrimdışı durumda `offline.html` gösterilir.

## Güvenlik

- Tüm POST istekleri CSRF token kontrolünden geçer.
- Oturum çerezleri `httponly` ve `samesite=Lax` olarak ayarlanır.
- Parolalar `password_hash` ile BCRYPT kullanılarak saklanır.
- Yüklenen avatarlar MIME ve boyut doğrulamasından geçirilir.

## Lisans

Bu proje MIT lisansı ile sunulur.
