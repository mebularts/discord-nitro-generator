# SolveClone

SolveClone, bulmaca/quiz topluluğu için soru-cevap odaklı bir PWA uygulamasıdır. PHP 7.3+ üzerinde framework kullanmadan geliştirilmiştir ve MySQL/MariaDB ile çalışır.

## Özellikler

- Kullanıcı kayıt/giriş, profil yönetimi ve görünürlük ayarları
- Anonim veya görünür soru gönderimi, cevaplama, bildirim üretimi
- Ana sayfa akışı ve profil sayfalarında public cevap listeleri
- Bildirim merkezi ve temel admin paneli (dashboard + ayarlar)
- PWA manifesti, service worker ve offline destekli deneyim
- CSRF koruması, brute-force sınırlaması, güvenli oturum ve rate limit
- Kurulum sihirbazı ile veritabanı tablolarının oluşturulması ve ilk admin hesabı

## Gereksinimler

- PHP 7.3 veya üzeri (PDO, JSON, mbstring uzantıları etkin)
- MySQL veya MariaDB
- Apache/Nginx (URL rewrite önerilir)

## Kurulum

1. Depoyu sunucunuza kopyalayın ve web sunucusu kökünü `public/` klasörüne yönlendirin.
2. `install/` dizinindeki sihirbazı çalıştırın:
   - Veritabanı bağlantısını test edin.
   - `install/install.sql` betiğini çalıştırarak tabloları oluşturun.
   - İlk admin kullanıcısını oluşturun.
3. Güvenlik için kurulum tamamlandıktan sonra `install/` dizinini kaldırın.
4. `.env` dosyasındaki veritabanı bilgilerini doğrulayın ve ek ortam değişkenlerini gerektiği gibi ekleyin.

## Ortam Değişkenleri

| Değişken | Açıklama | Varsayılan |
| --- | --- | --- |
| `APP_ENV` | `dev` veya `prod` | `prod` |
| `DB_DSN` | PDO DSN | `mysql:host=localhost;dbname=solveclone;charset=utf8mb4` |
| `DB_USER` | Veritabanı kullanıcı adı | `root` |
| `DB_PASS` | Veritabanı parolası | boş |
| `MAX_AVATAR_SIZE_MB` | Avatar yükleme limiti (MB) | `2` |
| `ALLOWED_SOCIALS` | Sosyal ağ whitelist (virgülle ayrılmış) | `instagram.com,twitter.com,x.com,youtube.com,tiktok.com,github.com,linkedin.com` |
| `RATE_LIMIT_LOGIN_PER_MIN` | Dakikadaki login deneme limiti | `5` |
| `MAINTENANCE_MODE` | Bakım modu (1: aktif) | `0` |

## Komut Dosyaları ve Dizinyapısı

```
app/                → Uygulama kodu (Config, Services, Controllers, Views, vb.)
public/             → Web kökü (index.php, assetler, service worker, manifest)
install/            → Kurulum sihirbazı ve SQL betiği
admin/              → Yönetim paneli giriş noktası (public/index.php'yi kullanır)
storage/uploads/    → Kullanıcı avatarları için klasör
app/logs/           → Uygulama logları (error_log)
```

## Geliştirme Notları

- Tüm POST isteklerinde CSRF token zorunludur.
- Service worker yalnızca GET isteklerini önbelleğe alır ve `/admin` yollarını cache dışı bırakır.
- Offline modda form gönderimleri engellenir ve kullanıcıya mesaj gösterilir.
- Bakım modunda (MAINTENANCE_MODE=1) admin olmayan kullanıcılar 503 görür.

## Lisans

MIT
