
# SolveClone (SolveOrDie eşdeğer)

PHP 7.3–8.3 uyumlu, Tailwind (prefix `tw-`) + Bootstrap 5 ile modern UI, MySQL/PDO tabanlı bulmaca sitesi. Türkçe admin panel, DeepL ile çok dillilik altyapısı (cache'li), PWA kurulum bildirimi ve oy sistemi içerir.

## Özellikler
- Filtrelenebilir bulmaca listesi (kategori, zorluk, uzunluk, popülerlik / yenilik)
- Detay sayfasında cevap gösterme, oy verme ve otomatik yüzde hesabı
- Tailwind + Bootstrap ile responsive arayüz, SEO meta etiketleri, canonical ve hreflang
- PWA manifest + service worker, ana ekrana ekleme banner'ı ve cihaz tavsiyeleri
- DeepL entegrasyonu için `translations` tablosu ile veritabanı cache'i
- Türkçe yönetim paneli üzerinden sorular, kullanıcılar, sayfalar ve ayarlar
- Ayarlar ekranından tema, reklam alanları, özel kod blokları, çok dillilik ve DeepL anahtarı yönetimi

## Kurulum
1. `composer` gerektirmez. Sunucunuzda `public/` kök dizin olarak ayarlayın (veya kökteki .htaccess ile /public'e yönlendirin).
2. `.env.example` dosyasını `.env` olarak kopyalayın ve veritabanı bilgilerini doldurun.
3. Tarayıcıdan `/admin/setup.php`'ye gidin. Tabloları oluşturup admin hesabını ekleyin.
4. Giriş: `/admin/login.php`
5. Soru eklemek için `/admin/riddles.php`

DeepL kullanmak için `.env` içine `DEEPL_API_KEY` girin ya da admin ayarlarına ekleyin (free hesap için `:fx` ile biter). Çeviri sonuçları `translations` tablosunda önbelleğe alınır.

PWA için `public/manifest.webmanifest` ve `public/sw.js` mevcuttur. Tailwind CDN konfigürasyonu ile `tw-` prefix aktiftir.

## Depolama Klasörleri
`storage/cache` ve `storage/logs` dizinleri yazılabilir olmalıdır. Sistem logları `storage/logs/app.log` dosyasına yazılır.

> Not: Bu paket başlangıç düzeyi MVP'dir. Kategori/etiket yönetim sayfaları ve otomatik çeviri batch işlemleri için ek sayfalar kolayca eklenebilir.
