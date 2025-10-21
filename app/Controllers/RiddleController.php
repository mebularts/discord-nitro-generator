<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Riddle;

class RiddleController
{
    public function show(string $slug): string
    {
        $riddle = Riddle::findBySlug($slug);
        if (!$riddle) {
            abort(404, __('riddles.not_found'));
        }

        $related = Riddle::related((int) $riddle['id']);
        $stats   = Riddle::voteStats((int) $riddle['id']);

        view_share(['meta' => [
            'title'       => $riddle['title'] . ' • ' . setting('app_name', 'SolveClone'),
            'description' => mb_substr(strip_tags($riddle['body']), 0, 160),
            'image'       => app_icon_url('512x512'),
        ]]);

        return view('riddle/show', [
            'riddle'  => $riddle,
            'related' => $related,
            'stats'   => $stats,
        ]);
    }
}
