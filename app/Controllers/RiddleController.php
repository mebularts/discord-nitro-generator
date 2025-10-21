<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Riddle;

use function abort;

require_once __DIR__ . '/../helpers.php';

class RiddleController
{
    public function show(string $slug): void
    {
        $riddle = Riddle::findBySlug($slug);
        if (!$riddle) {
            abort(404);
        }

        Riddle::incrementViews((int) $riddle['id']);
        $related = Riddle::related((int) $riddle['id'], (string) $riddle['difficulty']);

        view('riddle/show.php', [
            'riddle'  => $riddle,
            'related' => $related,
        ]);
    }
}
