<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Page;
use App\Models\Riddle;

require_once __DIR__ . '/../helpers.php';

class StaticController
{
    public function page(?string $slug = null): void
    {
        if ($slug === null) {
            $slug = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/');
        }
        if ($slug === '') {
            redirect('/');
        }
        $page = Page::findBySlug($slug);
        if (!$page) {
            http_response_code(404);
            exit('Not Found');
        }
        view('static/page.php', ['p' => $page]);
    }

    public function sitemap(): void
    {
        $entries = Riddle::sitemapEntries();
        $pages = Page::all();
        $base = rtrim(base_url(), '/');

        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        echo '<url><loc>' . h($base . '/') . '</loc></url>';
        foreach ($pages as $page) {
            echo '<url><loc>' . h($base . '/' . $page['slug']) . '</loc></url>';
        }
        foreach ($entries as $entry) {
            $loc = $base . '/riddle/' . $entry['slug'];
            $lastmod = $entry['updated_at'] ?: $entry['published_at'];
            echo '<url><loc>' . h($loc) . '</loc>';
            if ($lastmod) {
                echo '<lastmod>' . date(DATE_W3C, strtotime((string) $lastmod)) . '</lastmod>';
            }
            echo '</url>';
        }
        echo '</urlset>';
    }

    public function robots(): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        echo "User-agent: *\n";
        echo "Allow: /\n\n";
        echo 'Sitemap: ' . base_url('sitemap.xml');
    }
}
