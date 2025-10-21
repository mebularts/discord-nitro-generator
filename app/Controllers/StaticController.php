<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Page;

class StaticController
{
    public function show(string $slug): string
    {
        $page = Page::findBySlug($slug);
        if (!$page) {
            abort(404, __('errors.not_found'));
        }

        view_share(['meta' => [
            'title'       => $page['title'] . ' • ' . setting('app_name', 'SolveClone'),
            'description' => mb_substr(strip_tags($page['body']), 0, 160),
            'image'       => app_icon_url('512x512'),
        ]]);

        return view('static/page', ['page' => $page]);
    }
}
