<?php
namespace App\Services;

use App\Models\CountryRepository;
use App\Models\OrderRepository;
use App\Models\ServiceRepository;
use App\Models\UserRepository;
use App\Services\Sms\ProviderFactory;
use App\Services\Sms\SmsProviderInterface;

class OrderService
{
    /** @var UserRepository */
    private $users;
    /** @var ServiceRepository */
    private $services;
    /** @var CountryRepository */
    private $countries;
    /** @var OrderRepository */
    private $orders;
    /** @var SmsProviderInterface */
    private $provider;

    public function __construct()
    {
        $this->users = new UserRepository();
        $this->services = new ServiceRepository();
        $this->countries = new CountryRepository();
        $this->orders = new OrderRepository();
        $this->provider = ProviderFactory::make();
    }

    public function purchaseService(array $user, int $serviceId, int $countryId): array
    {
        $service = $this->services->find($serviceId);
        $country = $this->countries->find($countryId);

        if (!$service || !$country) {
            return ['message' => 'Seçilen servis veya ülke bulunamadı.'];
        }

        $price = $this->determinePrice($serviceId, $countryId, $service['base_price']);

        if ((float) $user['balance'] < $price) {
            return [
                'message' => 'Yetersiz bakiye. Lütfen bakiye yükleyin.',
                'reply_markup' => json_encode([
                    'inline_keyboard' => [[
                        ['text' => 'Bakiye Yükle', 'callback_data' => 'menu:balance'],
                    ]],
                ]),
            ];
        }

        try {
            $orderData = $this->provider->requestNumber((string) $serviceId, (string) $country['code']);
        } catch (\Throwable $exception) {
            return ['message' => 'Numara alınırken hata oluştu: ' . $exception->getMessage()];
        }

        $phone = $orderData['phone'] ?? 'Bilinmiyor';
        $providerOrderId = $orderData['id'] ?? null;

        $order = $this->orders->create([
            'user_id' => $user['id'],
            'service_id' => $serviceId,
            'country_id' => $countryId,
            'provider' => get_class($this->provider),
            'provider_order_id' => $providerOrderId,
            'phone_number' => $phone,
            'status' => 'pending',
            'cost' => $price,
        ]);

        $this->users->decrementBalance($user['id'], $price);

        $message = sprintf("Numaranız: %s\nSipariş durumunu onay kodu geldiğinde güncelleyeceğiz.", $phone);

        return ['message' => $message];
    }

    private function determinePrice(int $serviceId, int $countryId, float $fallback): float
    {
        $db = $this->countries;
        $countries = $db->allForService($serviceId);
        foreach ($countries as $country) {
            if ((int) $country['id'] === $countryId) {
                return (float) $country['price'];
            }
        }

        return $fallback;
    }
}
