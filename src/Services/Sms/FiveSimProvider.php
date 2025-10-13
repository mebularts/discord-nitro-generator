<?php
namespace App\Services\Sms;

use App\Support\Config;

class FiveSimProvider implements SmsProviderInterface
{
    /** @var string */
    private $apiKey;
    /** @var string */
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = Config::get('providers.5sim.api_key', '');
        $this->baseUrl = rtrim(Config::get('providers.5sim.base_url', 'https://5sim.net/v1'), '/');
    }

    public function getServices(): array
    {
        return $this->request('/guest/products/telegram');
    }

    public function getCountriesForService(string $serviceId): array
    {
        return $this->request('/guest/countries');
    }

    public function requestNumber(string $serviceId, string $countryCode): array
    {
        if (!$this->apiKey) {
            return [
                'id' => 'demo-order',
                'phone' => '+10000000000',
                'price' => 1.0,
            ];
        }

        $endpoint = sprintf('/user/buy/activation/%s/any/%s', $countryCode, $serviceId);
        return $this->request($endpoint, 'POST');
    }

    public function getSmsCode(string $orderId): ?string
    {
        if (!$this->apiKey) {
            return null;
        }

        $response = $this->request('/user/check/' . $orderId);
        return $response['sms'][0]['code'] ?? null;
    }

    public function cancelOrder(string $orderId): void
    {
        if (!$this->apiKey) {
            return;
        }

        $this->request('/user/cancel/' . $orderId, 'POST');
    }

    private function request(string $endpoint, string $method = 'GET'): array
    {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($this->apiKey) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->apiKey,
                'Accept: application/json',
            ]);
        }

        $response = curl_exec($ch);
        if ($response === false) {
            throw new \RuntimeException('5Sim isteği başarısız: ' . curl_error($ch));
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode >= 400) {
            throw new \RuntimeException('5Sim API hatası: ' . $response);
        }

        $data = json_decode($response, true);
        return $data ?: [];
    }
}
