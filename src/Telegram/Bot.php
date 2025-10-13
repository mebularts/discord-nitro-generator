<?php
namespace App\Telegram;

use App\Models\CountryRepository;
use App\Models\OrderRepository;
use App\Models\ServiceRepository;
use App\Models\UserRepository;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Support\Config;

class Bot
{
    /** @var TelegramClient */
    private $client;
    /** @var UserRepository */
    private $users;
    /** @var ServiceRepository */
    private $services;
    /** @var CountryRepository */
    private $countries;
    /** @var OrderRepository */
    private $orders;
    /** @var OrderService */
    private $orderService;
    /** @var PaymentService */
    private $paymentService;

    public function __construct()
    {
        $this->client = new TelegramClient();
        $this->users = new UserRepository();
        $this->services = new ServiceRepository();
        $this->countries = new CountryRepository();
        $this->orders = new OrderRepository();
        $this->orderService = new OrderService();
        $this->paymentService = new PaymentService();
    }

    public function handle(array $update): void
    {
        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        } elseif (isset($update['callback_query'])) {
            $this->handleCallback($update['callback_query']);
        }
    }

    private function handleMessage(array $message): void
    {
        $chatId = $message['chat']['id'];
        $from = $message['from'];
        $text = trim($message['text'] ?? '');

        $user = $this->users->findByTelegramId((string) $from['id']);
        if (!$user) {
            $user = $this->users->create([
                'telegram_id' => (string) $from['id'],
                'username' => $from['username'] ?? null,
                'first_name' => $from['first_name'] ?? null,
                'last_name' => $from['last_name'] ?? null,
            ]);
        }

        if (strpos($text, '/start') === 0) {
            $this->sendWelcome($chatId, $user);
            return;
        }

        if (strpos($text, '/s') === 0) {
            $keyword = trim(substr($text, 2));
            $this->searchServices($chatId, $keyword ?: '');
            return;
        }

        if (strpos($text, '/u') === 0) {
            $keyword = trim(substr($text, 2));
            $this->searchCountries($chatId, $keyword ?: '');
            return;
        }

        $this->sendMenu($chatId, $user);
    }

    private function handleCallback(array $callback): void
    {
        $data = $callback['data'] ?? '';
        $chatId = $callback['message']['chat']['id'];
        $messageId = $callback['message']['message_id'];
        $from = $callback['from'];

        $this->client->sendRequest('answerCallbackQuery', [
            'callback_query_id' => $callback['id'],
            'text' => '',
            'show_alert' => false,
        ]);

        $user = $this->users->findByTelegramId((string) $from['id']);
        if (!$user) {
            $user = $this->users->create([
                'telegram_id' => (string) $from['id'],
                'username' => $from['username'] ?? null,
                'first_name' => $from['first_name'] ?? null,
                'last_name' => $from['last_name'] ?? null,
            ]);
        }

        if (strpos($data, 'menu:') === 0) {
            $this->handleMenuSelection($chatId, $messageId, substr($data, 5), $user);
            return;
        }

        if (strpos($data, 'service:') === 0) {
            $serviceId = (int) substr($data, 8);
            $this->showCountries($chatId, $messageId, $serviceId);
            return;
        }

        if (strpos($data, 'country:') === 0) {
            $parts = explode(':', substr($data, 8));
            if (count($parts) === 2) {
                $serviceId = (int) $parts[0];
                $countryId = (int) $parts[1];
                $this->handlePurchase($chatId, $messageId, $user, $serviceId, $countryId);
            } else {
                $this->client->sendRequest('editMessageText', [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => 'Geçersiz ülke seçimi alındı.',
                ]);
            }
            return;
        }

        if (strpos($data, 'pay:') === 0) {
            $method = substr($data, 4);
            $this->handleTopUp($chatId, $messageId, $user, $method);
            return;
        }

        $this->client->sendRequest('answerCallbackQuery', [
            'callback_query_id' => $callback['id'],
            'text' => 'Bilinmeyen seçim.',
            'show_alert' => false,
        ]);
    }

    private function sendWelcome(int $chatId, array $user): void
    {
        $firstName = trim($user['first_name'] ?? '');
        $greeting = $firstName !== '' ? "Merhaba {$firstName}!" : 'Merhaba!';
        $appName = Config::get('app.name', 'Telegram mağazası');
        $text = $greeting . "\n" . $appName . ' mağazasına hoş geldiniz.';
        $this->client->sendRequest('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
        ]);
        $this->sendMenu($chatId, $user);
    }

    private function sendMenu(int $chatId, array $user): void
    {
        $keyboard = [
            [['text' => '📦 Mağaza', 'callback_data' => 'menu:store']],
            [['text' => '🧾 Siparişlerim', 'callback_data' => 'menu:orders']],
            [['text' => '💰 Bakiyem', 'callback_data' => 'menu:balance']],
            [['text' => '☎️ İletişim', 'callback_data' => 'menu:contact']],
        ];

        $this->client->sendRequest('sendMessage', [
            'chat_id' => $chatId,
            'text' => 'Lütfen bir seçim yapın:',
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
        ]);
    }

    private function handleMenuSelection(int $chatId, int $messageId, string $selection, array $user): void
    {
        switch ($selection) {
            case 'store':
                $this->showStore($chatId, $messageId);
                break;
            case 'store_all':
                $this->showAllServices($chatId, $messageId);
                break;
            case 'orders':
                $this->showOrders($chatId, $messageId, $user['id']);
                break;
            case 'balance':
                $this->showBalance($chatId, $messageId, $user);
                break;
            case 'contact':
                $this->showContact($chatId, $messageId);
                break;
            default:
                $this->client->sendRequest('editMessageText', [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => 'Bilinmeyen menü seçimi.',
                ]);
        }
    }

    private function showStore(int $chatId, int $messageId): void
    {
        $services = $this->services->all(true);
        if (!$services) {
            $services = $this->services->all();
            if (!$services) {
                $this->client->sendRequest('editMessageText', [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => 'Henüz servis eklenmedi.',
                ]);
                return;
            }
        }

        $keyboard = [];
        foreach ($services as $service) {
            $keyboard[] = [[
                'text' => '⭐ ' . $service['name'],
                'callback_data' => 'service:' . $service['id'],
            ]];
        }
        $keyboard[] = [[
            'text' => 'Daha Fazla Gör',
            'callback_data' => 'menu:store_all',
        ]];

        $this->client->sendRequest('editMessageText', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => 'Popüler servisler:',
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
        ]);
    }

    private function showAllServices(int $chatId, int $messageId): void
    {
        $services = $this->services->all();
        if (!$services) {
            $this->client->sendRequest('editMessageText', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => 'Henüz servis bulunmuyor.',
            ]);
            return;
        }

        $keyboard = [];
        foreach ($services as $service) {
            $keyboard[] = [[
                'text' => $service['name'],
                'callback_data' => 'service:' . $service['id'],
            ]];
        }

        $this->client->sendRequest('editMessageText', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => 'Tüm servisler:',
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
        ]);
    }

    private function showCountries(int $chatId, int $messageId, int $serviceId): void
    {
        $service = $this->services->find($serviceId);
        if (!$service) {
            $this->client->sendRequest('editMessageText', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => 'Servis bulunamadı.',
            ]);
            return;
        }

        $countries = $this->countries->allForService($serviceId);
        if (!$countries) {
            $this->client->sendRequest('editMessageText', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $service['name'] . ' için uygun ülke bulunamadı.',
            ]);
            return;
        }

        $keyboard = [];
        foreach ($countries as $country) {
            $label = sprintf('%s (%s) - %s₺', $country['name'], $country['code'], number_format((float) $country['price'], 2));
            $keyboard[] = [[
                'text' => $label,
                'callback_data' => 'country:' . $serviceId . ':' . $country['id'],
            ]];
        }

        $this->client->sendRequest('editMessageText', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $service['name'] . ' için ülke seçin:',
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
        ]);
    }

    private function handlePurchase(int $chatId, int $messageId, array $user, int $serviceId, int $countryId): void
    {
        $result = $this->orderService->purchaseService($user, $serviceId, $countryId);

        $this->client->sendRequest('editMessageText', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $result['message'],
            'reply_markup' => $result['reply_markup'] ?? null,
        ]);
    }

    private function handleTopUp(int $chatId, int $messageId, array $user, string $method): void
    {
        $response = $this->paymentService->createTopUp($user, $method);

        $this->client->sendRequest('editMessageText', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $response['message'],
            'reply_markup' => $response['reply_markup'] ?? null,
        ]);
    }

    private function showOrders(int $chatId, int $messageId, int $userId): void
    {
        $orders = $this->orders->forUser($userId);
        if (!$orders) {
            $text = 'Henüz siparişiniz bulunmuyor.';
        } else {
            $lines = [];
            foreach ($orders as $order) {
                $lines[] = sprintf('#%d %s - %s (%s) Durum: %s', $order['id'], $order['service_name'], $order['country_name'], $order['phone_number'] ?? 'bekleniyor', strtoupper($order['status']));
            }
            $text = implode("\n", $lines);
        }

        $this->client->sendRequest('editMessageText', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
        ]);
    }

    private function showBalance(int $chatId, int $messageId, array $user): void
    {
        $balance = number_format((float) $user['balance'], 2);
        $keyboard = $this->paymentService->topUpKeyboard();

        $this->client->sendRequest('editMessageText', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => "Mevcut bakiyeniz: {$balance}₺",
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
        ]);
    }

    private function showContact(int $chatId, int $messageId): void
    {
        $adminChat = Config::get('telegram.admin_chat_id');
        $text = 'Destek için lütfen aşağıdaki kanalları kullanın:';
        if ($adminChat) {
            if (is_numeric($adminChat)) {
                $text .= "\nAdmin Chat ID: " . $adminChat;
            } else {
                $text .= "\nAdmin: @" . ltrim((string) $adminChat, '@');
            }
        }

        $this->client->sendRequest('editMessageText', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
        ]);
    }

    private function searchServices(int $chatId, string $keyword): void
    {
        if ($keyword === '') {
            $this->client->sendRequest('sendMessage', [
                'chat_id' => $chatId,
                'text' => '/s komutunu servisin adı ile kullanın.',
            ]);
            return;
        }

        $results = $this->services->search($keyword);
        if (!$results) {
            $this->client->sendRequest('sendMessage', [
                'chat_id' => $chatId,
                'text' => 'Sonuç bulunamadı.',
            ]);
            return;
        }

        $lines = [];
        foreach ($results as $service) {
            $lines[] = sprintf('%s (#%d)', $service['name'], $service['id']);
        }

        $this->client->sendRequest('sendMessage', [
            'chat_id' => $chatId,
            'text' => implode("\n", $lines),
        ]);
    }

    private function searchCountries(int $chatId, string $keyword): void
    {
        if ($keyword === '') {
            $this->client->sendRequest('sendMessage', [
                'chat_id' => $chatId,
                'text' => '/u komutunu ülke adı veya kodu ile kullanın.',
            ]);
            return;
        }

        $results = $this->countries->search($keyword);
        if (!$results) {
            $this->client->sendRequest('sendMessage', [
                'chat_id' => $chatId,
                'text' => 'Sonuç bulunamadı.',
            ]);
            return;
        }

        $lines = [];
        foreach ($results as $country) {
            $lines[] = sprintf('%s (%s) - #%d', $country['name'], $country['code'], $country['id']);
        }

        $this->client->sendRequest('sendMessage', [
            'chat_id' => $chatId,
            'text' => implode("\n", $lines),
        ]);
    }
}
