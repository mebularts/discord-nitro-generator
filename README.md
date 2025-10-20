
# SolveClone (SolveOrDie eşdeğer)

PHP 7.3–8.3 uyumlu, Tailwind (prefix `tw-`) + Bootstrap 5 ile modern UI, MySQL/PDO tabanlı bulmaca sitesi. Türkçe admin panel, DeepL ile çok dillilik altyapısı (basit sınıf) ve PWA iskeleti içerir.

## Kurulum
1. `composer` gerektirmez. Sunucunuzda `public/` kök dizin olarak ayarlayın (veya kökteki .htaccess ile /public'e yönlendirin).
2. `.env.example` dosyasını `.env` olarak kopyalayın ve veritabanı bilgilerini doldurun.
3. Tarayıcıdan `/admin/setup.php`'ye gidin. Tabloları oluşturup admin hesabını ekleyin.
4. Giriş: `/admin/login.php`
5. Soru eklemek için `/admin/riddles.php`

DeepL kullanmak için `.env` içine `DEEPL_API_KEY` girin (free hesap için `:fx` ile biter).

PWA için `public/manifest.webmanifest` ve `public/sw.js` mevcuttur. Tailwind CDN konfigürasyonu ile `tw-` prefix aktiftir.

> Not: Bu paket başlangıç düzeyi MVP'dir. Kategori/etiket yönetim sayfaları ve otomatik çeviri batch işlemleri için ek sayfalar kolayca eklenebilir.
