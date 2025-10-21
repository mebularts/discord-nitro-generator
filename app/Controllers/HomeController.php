<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Category;
use App\Models\Riddle;

class HomeController
{
    public function __invoke(): string
    {
        $filters = [
            'category'   => $_GET['category'] ?? null,
            'difficulty' => $_GET['difficulty'] ?? null,
            'length'     => $_GET['length'] ?? null,
        ];

        $page    = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = 12;

        $pagination = Riddle::paginated($filters, $perPage, $page);
        $categories = Category::all();

        $title = __('home.title');
        view_share(['meta' => array_merge(view_shared()['meta'] ?? [], [
            'title'       => $title . ' • ' . setting('app_name', 'SolveClone'),
            'description' => __('home.description'),
        ])]);

        return view('home', [
            'filters'    => $filters,
            'categories' => $categories,
            'pagination' => $pagination,
        ]);
    }
}
