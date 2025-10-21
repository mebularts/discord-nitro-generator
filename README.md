# SolveClone

SolveClone is a lightweight, framework-free PHP puzzle platform inspired by SolveOrDie. It ships with a multilingual public website, a mobile-first PWA front-end and a dark, app-like administration area.

## Features

- 🔍 Filter riddles by category, difficulty and reading length
- 📱 Responsive UI powered by Bootstrap 5 and Tailwind (CDN) with an installable PWA shell
- 🌐 Multi-language support with file-based strings and optional DeepL auto-translation cache
- 👍 AJAX voting with duplicate detection by IP/user agent hash
- 🧩 Rich admin panel for riddles, static pages, users and settings (no external framework)
- ⚙️ One-click installer that provisions the database and bootstrap admin user

## Requirements

- PHP 7.3 or newer (tested up to PHP 8.3)
- MySQL or MariaDB 10+
- Composer is optional (no vendor dependencies are required)
- Web server capable of rewriting requests to `public/index.php`

## Quick start

1. Clone the repository to your PHP host and configure the web root to `public/`.
2. Ensure the `/storage` directory is writable by the web server user.
3. Visit `/admin/setup.php` in your browser and fill in the database and admin account details.
4. Log into `/admin/login.php` with the credentials created during setup.
5. Add riddles, categories and pages from the admin panel. The public site updates immediately.

## Environment file

The installer creates a `.env` file similar to:

```env
APP_ENV=production
APP_TIMEZONE=UTC
DEFAULT_LOCALE=en
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=solveclone
DB_USERNAME=root
DB_PASSWORD=secret
```

Update values as needed (e.g. to enable DeepL translations add `DEEPL_AUTH_KEY=your-key`).

## Development tips

- To run locally with PHP's built-in server: `php -S 127.0.0.1:8000 -t public`
- The service worker and manifest are available under `/sw.js` and `/manifest.webmanifest`.
- Language strings live in `app/lang`. Add new locales by duplicating `en.php` and translating the keys.
- Cached translation responses are stored under `storage/cache/translations`.

## Testing

Run a syntax check across all PHP files:

```bash
find . -name "*.php" -not -path "./vendor/*" -print0 | xargs -0 -n1 php -l
```

## License

Released under the MIT License. See `LICENSE` for details.
