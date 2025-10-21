<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\NotificationRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\UserRepository;
use App\Support\Helpers;
use InvalidArgumentException;

final class QuestionService
{
    private QuestionRepository $questions;
    private NotificationRepository $notifications;
    private UserRepository $users;

    public function __construct()
    {
        $this->questions = new QuestionRepository();
        $this->notifications = new NotificationRepository();
        $this->users = new UserRepository();
    }

    public function create(?int $fromUserId, int $toUserId, string $body, bool $anonymous): array
    {
        $body = trim($body);
        if ($body === '' || mb_strlen($body) > 2000) {
            throw new InvalidArgumentException('Soru metni boş olamaz ve 2000 karakteri geçemez.');
        }

        $target = $this->users->findById($toUserId);
        if (!$target) {
            throw new InvalidArgumentException('Hedef kullanıcı bulunamadı.');
        }

        $now = Helpers::now();
        $questionId = $this->questions->create([
            'from_user_id' => $anonymous ? null : $fromUserId,
            'to_user_id' => $toUserId,
            'body' => $body,
            'is_anonymous' => $anonymous ? 1 : 0,
            'created_at' => $now,
        ]);

        $this->notifications->create([
            'user_id' => $toUserId,
            'type' => 'new_question',
            'data_json' => json_encode(['question_id' => $questionId], JSON_THROW_ON_ERROR),
            'created_at' => $now,
        ]);

        return $this->questions->findById($questionId) ?? [];
    }

    public function inbox(int $userId, int $page, int $perPage): array
    {
        return $this->questions->findInbox($userId, $page, $perPage);
    }

    public function find(int $id): ?array
    {
        return $this->questions->findById($id);
    }
}
