<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Category;
use App\Models\Riddle;

use function cache_remember;

require_once __DIR__ . '/../helpers.php';

class HomeController
{
    public function index(): void
    {
        $filters = [
            'q'          => trim($_GET['q'] ?? ''),
            'difficulty' => $_GET['difficulty'] ?? null,
            'length'     => $_GET['length'] ?? null,
            'category'   => $_GET['category'] ?? null,
            'page'       => max(1, (int) ($_GET['page'] ?? 1)),
            'per_page'   => 20,
            'sort'       => $_GET['sort'] ?? 'pop',
        ];

        $data = Riddle::paginate($filters);
        $categories = Category::all();
        $stats = Riddle::stats();
        $trending = cache_remember('home:trending', 300, static fn () => Riddle::topVoted(4));
        $recent = cache_remember('home:recent', 300, static fn () => Riddle::recent(4));

        view('home.php', [
            'items'      => $data['items'],
            'total'      => $data['total'],
            'page'       => $data['page'],
            'per'        => $data['per'],
            'filters'    => $filters,
            'categories' => $categories,
            'stats'      => $stats,
            'trending'   => $trending,
            'recent'     => $recent,
        ]);
    }

    public function latest(): void
    {
        $_GET['sort'] = 'new';
        $this->index();
    }
}
