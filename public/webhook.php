<?php
require __DIR__ . '/../bot/bootstrap.php';

use App\Support\Config;
use App\Telegram\Bot;

$secret = Config::get('telegram.webhook_secret');
if ($secret && ($_GET['secret'] ?? '') !== $secret) {
    http_response_code(401);
    echo 'Unauthorized';
    exit;
}

$input = file_get_contents('php://input');
$update = json_decode($input, true);

if (!$update) {
    http_response_code(400);
    echo 'Invalid update';
    exit;
}

try {
    $bot = new Bot();
} catch (\Throwable $exception) {
    error_log('[webhook] Bot init failed: ' . $exception->getMessage());
    http_response_code(500);
    echo 'Bot configuration error';
    exit;
}

try {
    $bot->handle($update);
} catch (\Throwable $exception) {
    error_log('[webhook] Bot handle failed: ' . $exception->getMessage());
    http_response_code(500);
    echo 'Internal error';
    exit;
}

echo 'OK';
