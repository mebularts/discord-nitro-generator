<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AnswerRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\QuestionRepository;
use App\Support\Helpers;
use InvalidArgumentException;
use RuntimeException;

final class AnswerService
{
    private AnswerRepository $answers;
    private QuestionRepository $questions;
    private NotificationRepository $notifications;

    public function __construct()
    {
        $this->answers = new AnswerRepository();
        $this->questions = new QuestionRepository();
        $this->notifications = new NotificationRepository();
    }

    public function create(int $userId, int $questionId, string $body, bool $isPublic): array
    {
        $question = $this->questions->findById($questionId);
        if (!$question) {
            throw new RuntimeException('Soru bulunamadı.');
        }
        if ((int) $question['to_user_id'] !== $userId) {
            throw new RuntimeException('Bu soruya cevap veremezsin.');
        }
        if ($this->answers->findByQuestionId($questionId)) {
            throw new RuntimeException('Bu soruya zaten cevap verilmiş.');
        }

        $body = trim($body);
        if ($body === '' || mb_strlen($body) > 3000) {
            throw new InvalidArgumentException('Cevap boş olamaz ve 3000 karakteri geçemez.');
        }

        $now = Helpers::now();
        $answerId = $this->answers->create([
            'question_id' => $questionId,
            'user_id' => $userId,
            'body' => $body,
            'is_public' => $isPublic ? 1 : 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->notifications->create([
            'user_id' => $question['to_user_id'],
            'type' => 'new_answer',
            'data_json' => json_encode(['answer_id' => $answerId, 'question_id' => $questionId], JSON_THROW_ON_ERROR),
            'created_at' => $now,
        ]);
        if (!$question['is_anonymous'] && $question['from_user_id']) {
            $this->notifications->create([
                'user_id' => (int) $question['from_user_id'],
                'type' => 'new_answer',
                'data_json' => json_encode(['answer_id' => $answerId, 'question_id' => $questionId], JSON_THROW_ON_ERROR),
                'created_at' => $now,
            ]);
        }

        return $this->answers->findByQuestionId($questionId) ?? [];
    }

    public function toggleVisibility(int $userId, int $answerId, int $isPublic): void
    {
        $answer = $this->answers->findById($answerId);
        if (!$answer || (int) $answer['user_id'] !== $userId) {
            throw new RuntimeException('Cevabı güncelleme yetkin yok.');
        }
        $this->answers->updateVisibility($answerId, $isPublic);
    }

    public function publicAnswers(int $userId, int $page, int $perPage, string $sort): array
    {
        return $this->answers->findPublicByUser($userId, $page, $perPage, $sort);
    }
}
