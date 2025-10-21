# SolveClone — Üretim-Hazır Teknik & Ürün Spesi (Kod Yok)

## 0) Özet ve Hedef
- Ürün: Bulmaca/quiz içeriği + kullanıcı profilleri + soru-cevap etkileşimi + bildirimli PWA + admin panel.
- Platform: PHP 7.3 ve üzeri (8.x dahil), MySQL/MariaDB, Apache/Nginx (rewrite). Frameworksüz, PDO ile.
- Odak: Minimum bağımlılık, yüksek güvenlik, erişilebilir ve modern UI/UX, mobil öncelikli tasarım, offline çalışabilen deneyim.
- Pazarlama hedefi: Hızlı kurulum (setup wizard), sade konfig, tek sunuculu shared hosting uyumluluğu.

## 1) Kapsam ve Olmayanlar
- Kapsamda: Kullanıcı kayıt/giriş, profil kişiselleştirme, anonim/görünür soru, cevaplama, görünürlük kontrolleri, bildirimler, PWA offline desteği, admin yönetimi.
- Kapsam dışı (MVP): Gerçek zamanlı WebSocket, e-posta gönderimi (opsiyonel), çoklu dosya yüklemelerinde otomatik görsel kırpma, moderasyon için ML filtreleri.

## 2) Roller ve Yetkiler
- Ziyaretçi: İçerik görüntüleme (kısıtlı), public profil/cevapları görme, kayıt olma.
- Üye: Kendi profilini yönetme, soru sorma (anonim/görünür), gelen sorularını görme, cevap verme/görünürlük değiştirme, bildirim alma.
- Admin: Kullanıcı/Soru/Cevap/Bildirim listeleme-arama-filtreleme, spam/silme, site ayarları, log görüntüleme, bakım moduna alma.

## 3) Bilgi Mimarisi ve Navigasyon
- Ana Sayfa: Public cevap akışı (en yeni / en popüler), öne çıkan kullanıcı kartları.
- Profil Sayfası (kullanıcı adıyla erişilebilir kısa URL): Üst bant (profil rengi), avatar, bio, sosyal linkler, “Soru Sor” eylemi (modal), sekmeler:
  - Cevaplar (varsayılan, public olanlar)
  - Sorular (yalnız profil sahibine görünür)
  - Hakkında (bio + linkler)
- Bildirim Merkezi: Okunmamış/okunmuş, sayfalama.
- Ayarlar: Profil düzenleme, görünürlük (questions_public), kullanıcı adı değişimi (40 gün kuralı), avatar yükleme/silme, sosyal link whitelisti.
- Admin: Dashboard, kullanıcılar, sorular, cevaplar, bildirimler, ayarlar, loglar.

## 4) UI/UX İlkeleri
- Görsel dil: Modern, minimal, 8pt grid, yeterli boşluk, WCAG AA kontrast.
- Tema: Dark/Light (localStorage), varsayılan sistem temasına uyum; butonlarda hem ikon hem metin.
- Hata/başarı geri bildirimi: Açık dille toast/alert (kısa), detay log’a.
- Boş durum ekranları: “Neden boş?”, “Ne yapabilirim?” CTA.
- Form deneyimi: Net etiketler, zorunlu alan göstergesi, satır içi doğrulama mesajları.
- Erişilebilirlik: Klavye ile tam gezinme, focus ring, ikonlara aria-label, form alanlarında ilişkilendirilmiş label.

## 5) PWA ve Önbellekleme Kuralları
- Manifest: Uygulama adı, kısa adı, 192/512 ikon, start_url “/”, display “standalone”.
- Service Worker:
  - Sadece GET ve http/https.
  - /admin/* ve /admin/setup.php hiçbir koşulda cache’lenmez.
  - Yönlendirme (navigate): network-first; hata olduğunda offline.html.
  - Statik varlıklar: cache-first (same-origin, HTTP 200, response type basic).
  - CACHE_VERSION semver (“solveclone-vX.Y”) ve activate’te eski cache temizliği.
- Offline davranış:
  - Ana sayfa ve profil sayfaları önceden ziyaret edildiyse offline’da son cache gösterilir.
  - Form gönderimleri offline’da engellenir; kullanıcıya açıklayıcı mesaj.

## 6) Güvenlik Politikaları
- CSRF: Tüm POST istekleri token doğrulamalı.
- XSS: Tüm kullanıcı verileri HTML’e yazılırken escape’lenir.
- Oturum: httponly, samesite=Lax, HTTPS’te secure; logout tüm session’ı temizler.
- Brute force: Login denemesi IP başına kısa süreli limit, limit aşımında geçici blok.
- Dosya yükleme (avatar): MIME ve boyut kontrolü (maks 2 MB), güvenli dosya adı, rastgeleleştirme, yalnızca profil klasörüne yazım, URL üzerinden çalıştırılamaz (no-execute).
- HTTP başlıkları (öneri): CSP (self, img data: https:), X-Frame-Options: DENY, Referrer-Policy: no-referrer-when-downgrade, HSTS (HTTPS altında).
- Gizlilik: Anonim soru için from_user_id tutulmaz, is_anonymous=1 ile işaretlenir.

## 7) İş Kuralları (Sert)
- Kullanıcı adı değişimi: Son değişimden itibaren en az 40 gün geçmeden değiştirilemez; kalan gün UI’da gösterilir.
- Soru anonimliği: from_user_id boş, is_anonymous=1. (Admin; spam/gizlilik ihlali durumunda silebilir.)
- Cevap görünürlüğü: is_public alanı ile anlık değiştirilebilir; ana sayfada listelenme koşulu: cevap public VE profil questions_public=1.
- Profil rengi: Sadece #RRGGBB biçiminde, kayıt öncesi regex doğrulaması zorunlu.
- Sosyal linkler: Whitelist alan adları (instagram, twitter/x, youtube, tiktok, github, linkedin); tam URL doğrulaması.
- Bildirim üretimi: Yeni soru → hedef kullanıcıya “new_question”; soruya cevap → ilgili iki tarafa “new_answer” (soru sahibine ve gerekiyorsa soruyu sorana eğer anonim değilse).

## 8) Veri Modeli
(Alan adları ve türleri normatif tanımdır. Kod içermez.)
- users: id, email, password_hash, username, bio, profile_color, avatar, social_links, questions_public, username_changed_at, role, timestamps
- questions: id, from_user_id, to_user_id, body, is_anonymous, timestamps
- answers: id, question_id, user_id, body, is_public, timestamps
- notifications: id, user_id, type, data_json, is_read, timestamps
- settings: key, value

## 9) API Sözleşmeleri
Kimlik doğrulama session-cookie tabanlıdır. POST isteklerinde CSRF token zorunludur. JSON formatında başarı ve hata yanıtları döner.

## 10) Ekranlar ve Akışlar
- Kayıt / Giriş / Logout
- Profil (Cevaplar / Sorular / Ayarlar)
- Ana sayfa akışı
- Soru sor / Cevapla
- Bildirim listesi
- Admin Dashboard, Kullanıcılar, Sorular, Cevaplar, Ayarlar, Loglar

## 11) Performans ve Ölçüm
- Hedef TTI < 2.5s
- Görseller lazy-load, optimize
- Statik asset’ler fingerprint ve max-age uzun
- HTML no-cache

## 12) Kurulum ve Ortam Değişkenleri
- DB_DSN, DB_USER, DB_PASS, APP_ENV
- MAX_AVATAR_SIZE_MB, ALLOWED_SOCIALS, RATE_LIMIT_LOGIN_PER_MIN
- MAINTENANCE_MODE

## 13) Kurulum Sihirbazı Davranışı
- DB testi → install.sql → ilk admin → yönlendirme
- Hatalarda rollback
- Kurulum sonrası setup engeli önerisi

## 14) Hata Yönetimi
- Prod: display_errors=0, log_errors=1
- Log formatı: [context] message
- Kullanıcıya kısa açıklama, log’a detay

## 15) Test Planı
- Kurulum, Auth, Profil, Soru, Cevap, Bildirim, SW, Güvenlik, Erişilebilirlik

## 16) Kabul Kriterleri (Definition of Done)
Tüm akışların stabil çalışması, loglama, 40 gün kuralı, görünürlük mantığı, SW cache temizliği, admin işlevleri, hata toleransı.

## 17) Geliştirici Notları (AI için)
- Çıktı: Tek depoda public, app, install, admin klasörleri
- Kod: PSR-12, strict_types
- Framework: Yok (yalnız PDO, json, mbstring)
- Admin: role kontrolü, no-store yanıt
- Sayfalama: per_page=20
- Dil desteği opsiyonel: .json çeviri dosyaları
- Log’lar app/logs altında
- MAINTENANCE_MODE ile 503 bakım ekranı
