<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Request;
use App\Services\AnswerService;
use App\Support\Helpers;
use InvalidArgumentException;
use RuntimeException;

final class AnswerController extends Controller
{
    private AnswerService $answers;

    public function __construct()
    {
        $this->answers = new AnswerService();
    }

    public function create(Request $request): void
    {
        $user = $this->requireAuth();
        $this->validateCsrf($request);
        $questionId = (int) $request->input('question_id');
        $body = (string) $request->input('body');
        $isPublic = (bool) (int) $request->input('is_public', 1);
        try {
            $answer = $this->answers->create((int) $user['id'], $questionId, $body, $isPublic);
            Helpers::json(['success' => true, 'answer' => $answer]);
        } catch (InvalidArgumentException|RuntimeException $exception) {
            Helpers::json(['success' => false, 'error_code' => 'validation', 'message' => $exception->getMessage()], 422);
        }
    }

    public function toggle(Request $request): void
    {
        $user = $this->requireAuth();
        $this->validateCsrf($request);
        $answerId = (int) $request->input('answer_id');
        $isPublic = (int) $request->input('is_public', 1);
        try {
            $this->answers->toggleVisibility((int) $user['id'], $answerId, $isPublic);
            Helpers::json(['success' => true]);
        } catch (RuntimeException $exception) {
            Helpers::json([
                'success' => false,
                'error_code' => 'forbidden',
                'message' => $exception->getMessage(),
            ], 403);
        }
    }
}
