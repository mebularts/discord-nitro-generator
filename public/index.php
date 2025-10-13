<?php
require __DIR__ . '/../bot/bootstrap.php';

use App\Support\Config;

header('Content-Type: text/plain; charset=utf-8');
$appName = Config::get('app.name', 'Telegram SMS Mağazası');
echo $appName . " botu çalışıyor. Webhook için /webhook.php, yönetim paneli için /admin.php?token=... adresini kullanın.";
