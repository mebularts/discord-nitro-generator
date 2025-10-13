<?php
namespace App\Services;

use App\Models\PaymentRepository;
use App\Models\UserRepository;
use App\Support\Config;

class PaymentService
{
    /** @var PaymentRepository */
    private $payments;
    /** @var UserRepository */
    private $users;

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
}
