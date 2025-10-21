<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\NotificationRepository;

final class NotificationService
{
    private NotificationRepository $notifications;

    public function __construct()
    {
        $this->notifications = new NotificationRepository();
    }

    public function latest(int $userId, int $limit = 20): array
    {
        return $this->notifications->latestForUser($userId, $limit);
    }

    public function markAsRead(int $userId, ?array $ids = null): int
    {
        return $this->notifications->markAsRead($userId, $ids);
    }

    public function unreadCount(int $userId): int
    {
        return $this->notifications->unreadCount($userId);
    }
}
