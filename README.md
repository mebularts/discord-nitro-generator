
# SolveClone v2 – Modern SolveOrDie Alternative

SolveClone is a framework-free PHP 7.3–8.3 application that reimagines the SolveOrDie experience with a multilingual, mobile-first PWA design. The project ships with a Tailwind (via CDN, `tw-` prefix) + Bootstrap UI, DeepL translation cache, vote tracking, rich admin console, and installable offline support.

## Highlights

- **Responsive web app + PWA** – install prompt, offline cache via service worker, manifest, dedicated offline view, and app-like UI tuned for mobile and desktop.
- **Advanced riddle catalogue** – filter by difficulty, length, category, search keywords, or popularity. Cards show approval rate, categories, and quick access.
- **Riddle detail UX** – revealable answers, live AJAX voting with percentage feedback, sharing shortcuts, related riddles, view counters.
- **Multilingual layer** – locale switcher, DeepL-backed translation cache (`translations` table) and automatic UI string bootstrapping. Translations are stored per hash to avoid duplicate requests.
- **SEO ready** – canonical links, hreflang alternates, dynamic OG tags, robots.txt, sitemap.xml feed.
- **File-based cache** – helper utilities (`cache_remember`) writing to `storage/cache` for lightweight performance wins.
- **Admin suite (Turkish UI)** – dashboard metrics, riddle CRUD with category/tag attachments and DeepL batch warmup, bulk user factory, static page editor, comprehensive settings (ads, theme, locales, custom code injections).
- **Security fundamentals** – CSRF tokens, prepared statements, output escaping, vote deduplication via IP + UA hash, and session-gated admin routes.

## Project Structure

```
/public             → Router, assets, PWA manifest & service worker
/app                → Bootstrap, Router, Controllers, Models, Views, Translator
/admin              → Authenticated control panel (dashboard, riddles, users, pages, settings)
/install            → `install.sql` schema & seeds
/storage            → Cache & log folders (ensure writable)
```

## Getting Started

1. **Configure web root** – Point your vhost to `/public` or keep the top-level `.htaccess` to rewrite into it.
2. **Environment** – Duplicate `.env.example` to `.env`, update database credentials and optional DeepL key.
3. **Database** – Visit `/admin/setup.php` to run migrations (`install/install.sql`) and create the first admin user.
4. **Login** – `/admin/login.php` (default credentials from setup). Manage riddles, translations, settings.
5. **Frontend** – Access `/` for the fully responsive catalogue. Use `?lang=xx` to persist locale choice.

## Key Settings

- **Translations** – Provide `DEEPL_API_KEY` (or set via admin) to enable automatic caching. Missing locales fall back to DeepL on demand.
- **Theme & branding** – Configure logo, colors, meta description, and custom head/body injections from the settings panel.
- **Ads & custom code** – Header/sidebar/inline/footer slots plus custom CSS/JS fields are supported.

## Deployment Notes

- Requires PHP 7.3+ with PDO MySQL, cURL and mod_rewrite.
- `storage/cache` and `storage/logs` must be writable by the web server.
- Service worker caches `/`, `/assets/css/app.css`, `/assets/js/app.js`, and the manifest; bump `CACHE_VERSION` in `sw.js` to refresh clients.
- For production, adjust `.htaccess` headers or web server config to mirror the included cache & compression directives.

## Testing Checklist

- ✅ Admin setup and login create the database schema and administrator.
- ✅ Riddle filters and search respond instantly with modern UI feedback.
- ✅ Voting endpoint updates counts and percentage without page reloads.
- ✅ DeepL caching stores translations by content hash when an API key is available.
- ✅ PWA install prompt appears on supported browsers with offline caching.

Enjoy building your puzzle platform! Contributions and extensions (commenting, newsletter, OAuth, etc.) can be layered on this base without framework lock-in.
