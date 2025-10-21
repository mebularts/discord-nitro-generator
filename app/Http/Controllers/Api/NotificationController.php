<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Request;
use App\Services\NotificationService;

final class NotificationController extends Controller
{
    private NotificationService $notifications;

    public function __construct()
    {
        $this->notifications = new NotificationService();
    }

    public function latest(): void
    {
        $user = $this->requireAuth();
        $items = $this->notifications->latest((int) $user['id']);
        $this->json([
            'success' => true,
            'items' => $items,
            'unread_count' => $this->notifications->unreadCount((int) $user['id']),
        ]);
    }

    public function markRead(Request $request): void
    {
        $user = $this->requireAuth();
        $this->validateCsrf($request);
        $ids = $request->input('ids');
        $count = $this->notifications->markAsRead((int) $user['id'], is_array($ids) ? array_map('intval', $ids) : null);
        $this->json(['success' => true, 'updated' => $count]);
    }
}
