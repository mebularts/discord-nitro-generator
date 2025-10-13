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
        $response = $this->request('/guest/products');
        $services = [];

        if (isset($response['data']) && is_array($response['data'])) {
            $response = $response['data'];
        }

        foreach ($response as $key => $service) {
            if (is_array($service) && isset($service['id'])) {
                $services[] = [
                    'id' => (string) ($service['id'] ?? $key),
                    'name' => $service['name'] ?? ($service['product'] ?? ucfirst((string) $key)),
                    'price' => $service['price'] ?? ($service['cost'] ?? 0),
                    'category' => $service['category'] ?? ($service['product'] ?? null),
                    'popular' => ($service['count'] ?? 0) > 0,
                ];
            } elseif (is_array($service) && isset($service['products'])) {
                foreach ($service['products'] as $productKey => $product) {
                    $services[] = [
                        'id' => (string) ($product['id'] ?? $productKey),
                        'name' => $product['name'] ?? $productKey,
                        'price' => $product['price'] ?? ($product['cost'] ?? 0),
                        'category' => $service['category'] ?? $key,
                        'popular' => ($product['count'] ?? 0) > 0,
                    ];
                }
            }
        }

        return $services;
    }

    public function getCountriesForService(string $serviceId): array
    {
        $endpoint = '/guest/products/' . urlencode($serviceId);
        $response = $this->request($endpoint);
        $countries = [];

        if (isset($response['countries']) && is_array($response['countries'])) {
            $response = $response['countries'];
        }

        foreach ($response as $key => $country) {
            if (!is_array($country)) {
                continue;
            }

            $countries[] = [
                'provider_country_code' => (string) ($country['iso'] ?? $country['code'] ?? $key),
                'name' => $country['name'] ?? ($country['title'] ?? $key),
                'stock' => (int) ($country['count'] ?? $country['available'] ?? 0),
                'price' => (float) ($country['price'] ?? $country['cost'] ?? 0),
                'dial_prefix' => $country['prefix'] ?? null,
            ];
        }

        return $countries;
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
