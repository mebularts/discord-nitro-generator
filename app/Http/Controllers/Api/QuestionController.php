<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Request;
use App\Services\QuestionService;
use InvalidArgumentException;
use RuntimeException;

final class QuestionController extends Controller
{
    private QuestionService $questions;

    public function __construct()
    {
        $this->questions = new QuestionService();
    }

    public function create(Request $request): void
    {
        $user = $this->user();
        $this->validateCsrf($request);
        try {
            $question = $this->questions->create(
                $user['id'] ?? null,
                (int) $request->input('to_user_id'),
                (string) $request->input('body'),
                (bool) (int) $request->input('is_anonymous', 0)
            );
            $this->json(['success' => true, 'question' => $question]);
        } catch (InvalidArgumentException|RuntimeException $exception) {
            $this->json([
                'success' => false,
                'error_code' => 'validation_error',
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function inbox(Request $request): void
    {
        $user = $this->requireAuth();
        $page = max(1, (int) $request->input('page', 1));
        $perPage = max(1, min(50, (int) $request->input('per_page', 20)));
        $result = $this->questions->inbox((int) $user['id'], $page, $perPage);
        $this->json([
            'success' => true,
            'page' => $page,
            'per_page' => $perPage,
            'total' => $result['total'],
            'items' => $result['items'],
        ]);
    }
}
