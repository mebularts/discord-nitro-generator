<?php
namespace App\Services\Sms;

use App\Support\Config;

class SmsActivateProvider implements SmsProviderInterface
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = Config::get('providers.sms_activate.api_key', '');
        $this->baseUrl = rtrim(Config::get('providers.sms_activate.base_url', 'https://api.sms-activate.org'), '/');
    }

    public function getServices(): array
    {
        return [];
    }

    public function getCountriesForService(string $serviceId): array
    {
        return [];
    }

    public function requestNumber(string $serviceId, string $countryCode): array
    {
        if (!$this->apiKey) {
            return [
                'id' => 'demo-activate-order',
                'phone' => '+20000000000',
                'price' => 1.5,
            ];
        }

        $params = http_build_query([
            'api_key' => $this->apiKey,
            'action' => 'getNumberV2',
            'service' => $serviceId,
            'country' => $countryCode,
        ]);

        return $this->request('/stubs/handler_api.php?' . $params);
    }

    public function getSmsCode(string $orderId): ?string
    {
        if (!$this->apiKey) {
            return null;
        }

        $params = http_build_query([
            'api_key' => $this->apiKey,
            'action' => 'getStatus',
            'id' => $orderId,
        ]);

        $response = $this->request('/stubs/handler_api.php?' . $params, 'GET', false);
        if (isset($response['Status']) && $response['Status'] === 'STATUS_OK') {
            return $response['Code'] ?? null;
        }

        return null;
    }

    public function cancelOrder(string $orderId): void
    {
        if (!$this->apiKey) {
            return;
        }

        $params = http_build_query([
            'api_key' => $this->apiKey,
            'action' => 'setStatus',
            'status' => 8,
            'id' => $orderId,
        ]);

        $this->request('/stubs/handler_api.php?' . $params, 'GET', false);
    }

    private function request(string $endpoint, string $method = 'GET', bool $decodeJson = true)
    {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        $response = curl_exec($ch);
        if ($response === false) {
            throw new \RuntimeException('Sms-Activate isteği başarısız: ' . curl_error($ch));
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode >= 400) {
            throw new \RuntimeException('Sms-Activate API hatası: ' . $response);
        }

        if (!$decodeJson) {
            $parts = explode(':', trim($response));
            if ($parts[0] === 'STATUS_OK') {
                return ['Status' => 'STATUS_OK', 'Code' => $parts[1] ?? null];
            }

            return ['Status' => $parts[0] ?? 'UNKNOWN'];
        }

        $data = json_decode($response, true);
        return $data ?: [];
    }
}
