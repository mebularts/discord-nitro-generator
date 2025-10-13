<?php
require __DIR__ . '/bootstrap.php';

use App\Models\OrderRepository;
use App\Models\UserRepository;
use App\Services\Sms\ProviderFactory;
use App\Support\Database;
use App\Telegram\TelegramClient;

$orders = new OrderRepository();
$users = new UserRepository();
$telegram = new TelegramClient();
$provider = ProviderFactory::make();

$pending = Database::connection()->query("SELECT * FROM orders WHERE status = 'pending'")->fetchAll(\PDO::FETCH_ASSOC);

foreach ($pending as $order) {
    if (empty($order['provider_order_id'])) {
        continue;
    }

    try {
        $code = $provider->getSmsCode($order['provider_order_id']);
    } catch (\Throwable $exception) {
        continue;
    }

    if ($code) {
        $orders->update((int) $order['id'], ['status' => 'completed', 'code' => $code]);
        $user = $users->find((int) $order['user_id']);
        if ($user) {
            $telegram->sendRequest('sendMessage', [
                'chat_id' => $user['telegram_id'],
                'text' => sprintf('Sipariş #%d için doğrulama kodunuz: %s', $order['id'], $code),
            ]);
        }
    }
}
