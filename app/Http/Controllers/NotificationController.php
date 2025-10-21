<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Request;
use App\Services\NotificationService;

final class NotificationController extends Controller
{
    private NotificationService $notifications;

    public function __construct()
    {
        $this->notifications = new NotificationService();
    }

    public function index(Request $request): void
    {
        $user = $this->requireAuth();
        $items = $this->notifications->latest((int) $user['id']);
        $this->view('notifications/index', [
            'title' => 'Bildirimler',
            'notifications' => $items,
        ]);
    }

    public function markRead(Request $request): void
    {
        $user = $this->requireAuth();
        $this->validateCsrf($request);
        $ids = $request->input('ids');
        $this->notifications->markAsRead((int) $user['id'], is_array($ids) ? array_map('intval', $ids) : null);
        $this->redirect('/notifications');
    }
}
