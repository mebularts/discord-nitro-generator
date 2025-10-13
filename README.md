# Telegram SMS Mağaza Botu

Bu proje, PHP 7.3 ve üzeri sürümlerde çalışacak şekilde hazırlanmış, Telegram üzerinden SMS doğrulama servisleri satan bir bot ve yönetim paneli içerir. Kullanıcılar Telegram botu ile mağazayı gezebilir, sipariş geçmişlerini görüntüleyebilir, bakiyelerini takip edip bakiye yükleme talebi oluşturabilirler. Yönetim paneli aracılığıyla Telegram ve SMS sağlayıcı ayarları, ödeme yöntemleri ve kullanıcı hareketleri izlenebilir.

## Özellikler

- `/start` komutu ile otomatik kullanıcı kaydı ve mağaza menüsü
- Popüler servislerin öne çıktığı mağaza akışı, tüm servisleri görüntüleme ve `/s` ile servis arama
- Servise ait stoklu ülkeleri görüntüleme, `/u` komutu ile ülke arama
- Bakiye yeterliyse numara satın alma, yetersizse bakiye yükleme çağrısı
- Sağlayıcıdan gelen SMS kodunu kullanıcıya iletmek için kontrol betiği
- Telegram Stars, IBAN, kripto ve çevrim içi kripto için bakiye yükleme yönergeleri
- Yönetim panelinde Telegram & API anahtarlarını, ödeme yöntemlerini, kullanıcıları, ödemeleri ve servisleri görüntüleme / güncelleme

## Kurulum

1. Gerekli PHP uzantılarının (curl, pdo_sqlite) aktif olduğundan emin olun.
2. Depoyu sunucunuza kopyalayın ve proje dizinine geçin.
3. `.env.example` dosyasını `.env` olarak kopyalayın ve Telegram bot token, admin paneli anahtarı, SMS sağlayıcı API anahtarları ve ödeme bilgilerini doldurun. `APP_NAME` değeri karşılamada gösterilecek mağaza adını belirler.
4. Yazma izinleri için `storage/` klasörünü web sunucusunun yazabileceği şekilde ayarlayın. SQLite dosyası ilk çalıştırmada bu klasörde oluşturulur.
5. Varsayılan değerler `.env` üzerinden alınır; sunucuda farklı değerler kullanmak için ortam değişkenleri de tanımlayabilirsiniz.
6. İlk kurulumda veritabanı otomatik oluşturulur. Örnek servis ve ülke kayıtları eklemek için:

   ```bash
   php bot/seed.php
   ```

## Telegram Botunu Çalıştırma

1. Telegram BotFather üzerinden bir bot oluşturup token alın.
2. `ADMIN_PANEL_TOKEN`, `TELEGRAM_BOT_TOKEN`, `TELEGRAM_ADMIN_CHAT_ID` ve `TELEGRAM_WEBHOOK_SECRET` gibi değerleri ortam değişkeni olarak tanımlayın veya yönetim panelinden girin.
3. Sunucunuzdaki `public/webhook.php` dosyasını HTTPS üzerinden Telegram'a webhook olarak ayarlayın. Web sunucusunun `public/` klasörüne yönlendirildiğinden ve `.htaccess` dosyasının etkin olduğundan emin olun.

   ```bash
   curl -X POST "https://api.telegram.org/bot<bot_token>/setWebhook" \
        -d "url=https://alanadiniz.com/webhook.php?secret=<webhook_secret>"
   ```

4. Bot, kullanıcı mesajlarını webhook üzerinden alır ve otomatik olarak yanıtlar.

## SMS Sağlayıcıları

Varsayılan olarak 5Sim ve Sms-Activate desteklenir. API anahtarlarını yönetim panelinden girerek etkinleştirebilirsiniz. API anahtarı girilmemişse test amaçlı sahte numaralar döndürülür.

## SMS Kodlarını Kontrol Etme

Sağlayıcıdan gelen SMS kodlarını kullanıcıya aktarmak için periyodik olarak `bot/check_sms.php` dosyasını çalıştırın. Örnek cron:

```bash
*/2 * * * * php /path/to/project/bot/check_sms.php
```

Kod bulunduğunda kullanıcıya Telegram üzerinden bilgilendirme mesajı gönderilir.

## Yönetim Paneli

`public/admin.php` dosyasına tarayıcıdan `?token=ADMIN_PANEL_TOKEN` parametresi ile erişilir. `.htaccess` dosyası, istenmeyen dosya listelemeyi kapatır ve `.env` gibi hassas dosyalara erişimi engeller. Panel üzerinden:

- Telegram bot token, admin chat ID ve webhook gizli anahtarını güncelleyin.
- Varsayılan SMS sağlayıcısını seçip API anahtarlarını girin.
- Ödeme yöntemlerini etkinleştirip gerekli hesap bilgilerini düzenleyin.
- Kullanıcı, ödeme ve servis listelerini görüntüleyin.

## Komutlar

- `/start` – Kullanıcı kaydı oluşturur ve mağaza menüsünü gösterir.
- `/s <kelime>` – Servis arama.
- `/u <kelime>` – Ülke arama.

## Geliştirme İpuçları

- Yeni tablo veya alan eklerken `storage/schema.sql` dosyasını güncelleyin.
- Otomatik yükleyici `src/` altındaki sınıfları `App\` isim alanıyla yükler.
- Ek SMS sağlayıcıları eklemek için `App\Services\Sms\SmsProviderInterface` arayüzünü uygulayan sınıflar ekleyin ve `ProviderFactory` içerisine kaydedin.

## Lisans

Bu proje örnek amaçlı hazırlanmıştır ve ihtiyaçlarınıza göre düzenleyebilirsiniz.
