<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Request;
use App\Services\QuestionService;
use App\Support\Helpers;
use InvalidArgumentException;
use RuntimeException;

final class QuestionController extends Controller
{
    private QuestionService $questions;

    public function __construct()
    {
        $this->questions = new QuestionService();
    }

    public function inbox(Request $request): void
    {
        $user = $this->requireAuth();
        $page = max(1, (int) $request->input('page', 1));
        $questions = $this->questions->inbox((int) $user['id'], $page, 20);
        $this->view('profile/inbox', [
            'title' => 'Sorular',
            'items' => $questions,
            'page' => $page,
        ]);
    }

    public function create(Request $request): void
    {
        $this->validateCsrf($request);
        $user = $this->user();
        $toUser = (int) $request->input('to_user_id');
        $body = (string) $request->input('body');
        $anonymous = (bool) (int) $request->input('is_anonymous', 0);
        try {
            $question = $this->questions->create($user['id'] ?? null, $toUser, $body, $anonymous);
            Helpers::json(['success' => true, 'question' => $question]);
        } catch (InvalidArgumentException|RuntimeException $exception) {
            Helpers::json(['success' => false, 'error_code' => 'validation', 'message' => $exception->getMessage()], 422);
        }
    }
}
