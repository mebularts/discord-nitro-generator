<?php
require __DIR__ . '/bootstrap.php';

use App\Models\OrderRepository;
use App\Models\UserRepository;
use App\Services\Sms\ProviderFactory;
use App\Support\Database;
use App\Telegram\TelegramClient;

$orders = new OrderRepository();
$users = new UserRepository();
$telegram = null;

try {
    $telegram = new TelegramClient();
} catch (\Throwable $exception) {
    error_log('[check_sms] Telegram client init failed: ' . $exception->getMessage());
}

$pending = Database::connection()->query("SELECT * FROM orders WHERE status = 'pending'")->fetchAll(\PDO::FETCH_ASSOC);

foreach ($pending as $order) {
    if (empty($order['provider_order_id'])) {
        continue;
    }

    $provider = ProviderFactory::fromClass($order['provider'] ?? null);

    try {
        $code = $provider->getSmsCode($order['provider_order_id']);
    } catch (\Throwable $exception) {
        continue;
    }

    if ($code) {
        $orders->update((int) $order['id'], ['status' => 'completed', 'code' => $code]);
        $user = $users->find((int) $order['user_id']);
        if ($user && $telegram) {
            try {
                $telegram->sendRequest('sendMessage', [
                    'chat_id' => $user['telegram_id'],
                    'text' => sprintf('Sipariş #%d için doğrulama kodunuz: %s', $order['id'], $code),
                ]);
            } catch (\Throwable $exception) {
                error_log('[check_sms] Telegram notify failed: ' . $exception->getMessage());
            }
        }
    }
}
