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

$bot = new Bot();
$bot->handle($update);

echo 'OK';
