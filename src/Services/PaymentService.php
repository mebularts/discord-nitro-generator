<?php
namespace App\Services;

use App\Models\PaymentRepository;
use App\Models\UserRepository;
use App\Services\Payments\NowPaymentsClient;
use App\Support\Config;
use App\Telegram\TelegramClient;

class PaymentService
{
    /** @var PaymentRepository */
    private $payments;
    /** @var UserRepository */
    private $users;
    /** @var TelegramClient|null */
    private $telegram;
    /** @var NowPaymentsClient */
    private $nowPayments;

    public function __construct()
    {
        $this->payments = new PaymentRepository();
        $this->users = new UserRepository();
        $this->nowPayments = new NowPaymentsClient();

        try {
            $this->telegram = new TelegramClient();
        } catch (\Throwable $exception) {
            $this->telegram = null;
        }
    }

    public function topUpKeyboard(): array
    {
        $config = Config::get('payments');
        $keyboard = [];

        foreach ($config as $method => $options) {
            if (!($options['enabled'] ?? false)) {
                continue;
            }

            $keyboard[] = [[
                'text' => $this->labelForMethod($method),
                'callback_data' => 'pay:' . $method,
            ]];
        }

        return $keyboard ?: [[['text' => 'Destek ile görüşün', 'callback_data' => 'menu:contact']]];
    }

    public function createTopUp(array $user, string $method): array
    {
        $config = Config::get('payments.' . $method);
        if (!$config || !($config['enabled'] ?? false)) {
            return ['message' => 'Bu ödeme yöntemi kullanılabilir değil.'];
        }

        switch ($method) {
            case 'nowpayments':
                return $this->createNowPaymentsTopUp($user, $config);
            case 'telegram_stars':
                return $this->createTelegramStarsTopUp($user, $config);
            default:
                return $this->createManualTopUp($user, $method, $config);
        }
    }

    private function labelForMethod(string $method): string
    {
        switch ($method) {
            case 'telegram_stars':
                return 'Telegram Stars ile Yükle';
            case 'iban':
                return 'IBAN ile Ödeme';
            case 'crypto':
                return 'Kripto ile Ödeme';
            case 'online_crypto':
                return 'Online Kripto ile Ödeme';
            default:
                return ucfirst($method);
        }
    }

    private function instructionsForMethod(string $method, array $config): string
    {
        switch ($method) {
            case 'telegram_stars':
                return 'Telegram Stars ile ödeme yapmak için lütfen uygulama içindeki yönlendirmeleri takip edin.';
            case 'iban':
                return sprintf(
                    'Lütfen %s IBAN numarasına %s adına ödeme yapın ve dekontu iletin.',
                    $config['iban'] ?? '***',
                    $config['holder'] ?? '***'
                );
            case 'crypto':
                return sprintf('Aşağıdaki cüzdana transfer yapın: %s', $config['address'] ?? '***');
            case 'online_crypto':
                return sprintf('Online kripto sağlayıcısı: %s üzerinden ödeme yapabilirsiniz.', $config['provider'] ?? '***');
            default:
                return 'Ödeme talimatları için destek ile iletişime geçin.';
        }
    }

    private function createManualTopUp(array $user, string $method, array $config): array
    {
        $instructions = $this->instructionsForMethod($method, $config);

        $this->payments->create([
            'user_id' => $user['id'],
            'method' => $method,
            'amount' => 0,
            'status' => 'pending',
            'payload' => json_encode(['instructions' => $instructions]),
        ]);

        return [
            'message' => $instructions,
            'reply_markup' => json_encode(['inline_keyboard' => $this->topUpKeyboard()]),
        ];
    }

    private function createNowPaymentsTopUp(array $user, array $config): array
    {
        $amount = (float) ($config['default_amount'] ?? 100);
        $priceCurrency = $config['price_currency'] ?? 'USD';
        $payCurrency = $config['pay_currency'] ?? 'USDT';
        $successUrl = $config['success_url'] ?? (Config::get('app.url') . '/payments/success');
        $cancelUrl = $config['cancel_url'] ?? (Config::get('app.url') . '/payments/cancel');

        $payment = $this->payments->create([
            'user_id' => $user['id'],
            'method' => 'nowpayments',
            'amount' => $amount,
            'status' => 'pending',
            'payload' => null,
        ]);

        try {
            $invoice = $this->nowPayments->createInvoice($amount, $priceCurrency, $payCurrency, (string) $payment['id'], $successUrl, $cancelUrl);
        } catch (\Throwable $exception) {
            return [
                'message' => 'NowPayments faturası oluşturulamadı: ' . $exception->getMessage(),
            ];
        }

        $payload = [
            'invoice_id' => $invoice['id'] ?? null,
            'invoice_url' => $invoice['invoice_url'] ?? ($invoice['result']['invoice_url'] ?? null),
            'price_amount' => $invoice['price_amount'] ?? $amount,
            'currency' => $priceCurrency,
        ];
        $this->payments->update((int) $payment['id'], [
            'payload' => json_encode($payload),
        ]);

        $link = $payload['invoice_url'] ?? $successUrl;

        return [
            'message' => "NowPayments ile ödeme yapmak için aşağıdaki bağlantıya tıklayın:\n$link",
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => 'Ödemeyi Tamamla', 'url' => $link],
                    ],
                    [
                        ['text' => 'Diğer yöntemler', 'callback_data' => 'menu:balance'],
                    ],
                ],
            ]),
        ];
    }

    private function createTelegramStarsTopUp(array $user, array $config): array
    {
        if (!$this->telegram) {
            return $this->createManualTopUp($user, 'telegram_stars', $config);
        }

        $packages = $config['packages'] ?? [];
        if (!$packages) {
            $packages[] = [
                'label' => '100 Yıldız',
                'stars' => 100,
                'price' => 100,
            ];
        }

        $package = $packages[0];
        $payloadId = 'stars_' . $user['id'] . '_' . time();

        $params = [
            'title' => $config['title'] ?? 'Telegram Stars ile Bakiye Yükleme',
            'description' => $config['description'] ?? 'Ödemeyi tamamlamak için Telegram Stars satın alın.',
            'payload' => $payloadId,
            'currency' => 'XTR',
            'prices' => json_encode([
                [
                    'label' => $package['label'],
                    'amount' => (int) $package['stars'],
                ],
            ]),
        ];

        if (!empty($config['provider_token'])) {
            $params['provider_token'] = $config['provider_token'];
        }

        try {
            $invoice = $this->telegram->sendRequest('createInvoiceLink', $params);
        } catch (\Throwable $exception) {
            return $this->createManualTopUp($user, 'telegram_stars', $config);
        }

        $link = $invoice['result'] ?? null;
        if (!$link) {
            return $this->createManualTopUp($user, 'telegram_stars', $config);
        }

        $payment = $this->payments->create([
            'user_id' => $user['id'],
            'method' => 'telegram_stars',
            'amount' => (float) ($package['price'] ?? $package['stars']),
            'status' => 'pending',
            'payload' => json_encode([
                'invoice_link' => $link,
                'payload' => $payloadId,
                'stars' => $package['stars'],
            ]),
        ]);

        return [
            'message' => "Telegram Stars ile ödeme yapmak için bağlantıya tıklayın:\n$link",
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => 'Telegram Stars ile Öde', 'url' => $link],
                    ],
                    [
                        ['text' => 'Diğer yöntemler', 'callback_data' => 'menu:balance'],
                    ],
                ],
            ]),
        ];
    }
}
