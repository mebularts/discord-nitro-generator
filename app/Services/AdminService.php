<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;
use App\Repositories\AnswerRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\SettingRepository;
use App\Repositories\UserRepository;

final class AdminService
{
    private UserRepository $users;
    private QuestionRepository $questions;
    private AnswerRepository $answers;
    private NotificationRepository $notifications;
    private SettingRepository $settings;

    public function __construct()
    {
        $this->users = new UserRepository();
        $this->questions = new QuestionRepository();
        $this->answers = new AnswerRepository();
        $this->notifications = new NotificationRepository();
        $this->settings = new SettingRepository();
    }

    public function dashboardMetrics(): array
    {
        $db = Connection::get();
        return [
            'total_users' => (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'questions_24h' => (int) $db->query("SELECT COUNT(*) FROM questions WHERE created_at >= NOW() - INTERVAL 1 DAY")->fetchColumn(),
            'answers_24h' => (int) $db->query("SELECT COUNT(*) FROM answers WHERE created_at >= NOW() - INTERVAL 1 DAY")->fetchColumn(),
            'unread_notifications' => (int) $db->query('SELECT COUNT(*) FROM notifications WHERE is_read = 0')->fetchColumn(),
        ];
    }

    public function settings(): array
    {
        return $this->settings->all();
    }

    public function updateSetting(string $key, string $value): void
    {
        $this->settings->set($key, $value);
    }
}
