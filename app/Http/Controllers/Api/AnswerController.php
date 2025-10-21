<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Request;
use App\Services\AnswerService;
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
        try {
            $answer = $this->answers->create(
                (int) $user['id'],
                (int) $request->input('question_id'),
                (string) $request->input('body'),
                (bool) (int) $request->input('is_public', 1)
            );
            $this->json(['success' => true, 'answer' => $answer]);
        } catch (InvalidArgumentException|RuntimeException $exception) {
            $this->json([
                'success' => false,
                'error_code' => 'validation_error',
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function toggle(Request $request): void
    {
        $user = $this->requireAuth();
        $this->validateCsrf($request);
        try {
            $this->answers->toggleVisibility((int) $user['id'], (int) $request->input('answer_id'), (int) $request->input('is_public', 1));
            $this->json(['success' => true]);
        } catch (RuntimeException $exception) {
            $this->json([
                'success' => false,
                'error_code' => 'forbidden',
                'message' => $exception->getMessage(),
            ], 403);
        }
    }

    public function publicAnswers(Request $request): void
    {
        $usernameOrId = $request->input('user_id');
        $sort = $request->input('sort', 'newest');
        $page = max(1, (int) $request->input('page', 1));
        $perPage = max(1, min(50, (int) $request->input('per_page', 20)));
        $userRepo = new \App\Repositories\UserRepository();
        $user = is_numeric($usernameOrId) ? $userRepo->findById((int) $usernameOrId) : $userRepo->findByEmailOrUsername((string) $request->input('username'));
        if (!$user) {
            $this->json([
                'success' => false,
                'error_code' => 'user_not_found',
                'message' => 'Kullanıcı bulunamadı.',
            ], 404);
            return;
        }
        $answers = $this->answers->publicAnswers((int) $user['id'], $page, $perPage, in_array($sort, ['popular', 'newest'], true) ? $sort : 'newest');
        $this->json([
            'success' => true,
            'page' => $page,
            'per_page' => $perPage,
            'total' => $answers['total'],
            'items' => $answers['items'],
        ]);
    }
}
