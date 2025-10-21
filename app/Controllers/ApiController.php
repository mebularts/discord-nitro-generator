<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Riddle;

class ApiController
{
    public function vote(): void
    {
        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
            abort(400, 'Invalid request');
        }

        verify_csrf_token();

        $riddleId = (int) ($_POST['id'] ?? 0);
        $score    = (int) ($_POST['score'] ?? 0);
        if (!$riddleId || !in_array($score, [1, -1], true)) {
            json_response(['error' => 'Invalid payload'], 422);
        }

        $riddle = Riddle::findById($riddleId);
        if (!$riddle) {
            json_response(['error' => 'Riddle not found'], 404);
        }

        $hash = voter_hash();
        Riddle::recordVote((int) $riddle['id'], $score, $hash);
        $stats = Riddle::voteStats((int) $riddle['id']);
        json_response(['success' => true, 'stats' => $stats]);
    }
}
