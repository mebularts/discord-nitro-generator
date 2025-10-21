<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\Api as Api;
use App\Http\Middleware\MaintenanceMiddleware;
use App\Http\Request;
use App\Http\Router;
use App\Support\Helpers;

$request = new Request();

$maintenance = new MaintenanceMiddleware();
$allowed = $maintenance($request, static function () {
    return true;
});
if ($allowed === false) {
    return;
}

$router = new Router();

$router->get('/', [HomeController::class, 'index']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/settings', [ProfileController::class, 'settings']);
$router->post('/settings', [ProfileController::class, 'update']);
$router->post('/settings/username', [ProfileController::class, 'changeUsername']);
$router->get('/notifications', [NotificationController::class, 'index']);
$router->post('/notifications/read', [NotificationController::class, 'markRead']);
$router->get('/inbox', [QuestionController::class, 'inbox']);
$router->post('/questions', [QuestionController::class, 'create']);
$router->post('/answers', [AnswerController::class, 'create']);
$router->post('/answers/toggle', [AnswerController::class, 'toggle']);
$router->get('/profile/{username}', [ProfileController::class, 'show']);

// Admin
$router->get('/admin', [AdminController::class, 'dashboard']);
$router->get('/admin/settings', [AdminController::class, 'settings']);
$router->post('/admin/settings', [AdminController::class, 'updateSettings']);

// API routes
$router->post('/api/auth/register', [Api\AuthController::class, 'register']);
$router->post('/api/auth/login', [Api\AuthController::class, 'login']);
$router->post('/api/auth/logout', [Api\AuthController::class, 'logout']);
$router->get('/api/profile/me', [Api\ProfileController::class, 'me']);
$router->post('/api/profile/update', [Api\ProfileController::class, 'update']);
$router->post('/api/profile/username', [Api\ProfileController::class, 'changeUsername']);
$router->post('/api/profile/visibility', [Api\ProfileController::class, 'visibility']);
$router->post('/api/questions/create', [Api\QuestionController::class, 'create']);
$router->get('/api/questions/inbox', [Api\QuestionController::class, 'inbox']);
$router->post('/api/answers/create', [Api\AnswerController::class, 'create']);
$router->post('/api/answers/visibility', [Api\AnswerController::class, 'toggle']);
$router->get('/api/answers/public', [Api\AnswerController::class, 'publicAnswers']);
$router->get('/api/notifications/latest', [Api\NotificationController::class, 'latest']);
$router->post('/api/notifications/read', [Api\NotificationController::class, 'markRead']);
$router->get('/api/feed/home', [Api\FeedController::class, 'home']);

$router->dispatch($request);
