<?php
namespace App\Services;

use App\Models\PaymentRepository;
use App\Models\UserRepository;
use App\Support\Config;

class PaymentService
{
    private PaymentRepository $payments;
    private UserRepository $users;

    public function __construct()
    {
        $this->payments = new PaymentRepository();
        $this->users = new UserRepository();
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

    private function labelForMethod(string $method): string
    {
        return match ($method) {
            'telegram_stars' => 'Telegram Stars ile Yükle',
            'iban' => 'IBAN ile Ödeme',
            'crypto' => 'Kripto ile Ödeme',
            'online_crypto' => 'Online Kripto ile Ödeme',
            default => ucfirst($method),
        };
    }

    private function instructionsForMethod(string $method, array $config): string
    {
        return match ($method) {
            'telegram_stars' => 'Telegram Stars ile ödeme yapmak için lütfen uygulama içindeki yönlendirmeleri takip edin.',
            'iban' => sprintf("Lütfen %s IBAN numarasına %s adına ödeme yapın ve dekontu iletin.", $config['iban'] ?? '***', $config['holder'] ?? '***'),
            'crypto' => sprintf('Aşağıdaki cüzdana transfer yapın: %s', $config['address'] ?? '***'),
            'online_crypto' => sprintf('Online kripto sağlayıcısı: %s üzerinden ödeme yapabilirsiniz.', $config['provider'] ?? '***'),
            default => 'Ödeme talimatları için destek ile iletişime geçin.',
        };
    }
}
