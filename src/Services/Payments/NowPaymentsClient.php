<?php
namespace App\Services\Payments;

use App\Support\Config;

class NowPaymentsClient
{
    /** @var string */
    private $apiKey;
    /** @var string */
    private $baseUrl;

    public function __construct(?string $apiKey = null, ?string $baseUrl = null)
    {
        $this->apiKey = $apiKey ?: Config::get('payments.nowpayments.api_key', '');
        $this->baseUrl = rtrim($baseUrl ?: Config::get('payments.nowpayments.base_url', 'https://api.nowpayments.io/v1'), '/');
    }

    public function createInvoice(float $priceAmount, string $priceCurrency, string $payCurrency, string $orderId, string $successUrl, string $cancelUrl): array
    {
        if (!$this->apiKey) {
            return [
                'id' => 'demo-invoice',
                'order_id' => $orderId,
                'invoice_url' => $successUrl,
                'price_amount' => $priceAmount,
                'price_currency' => $priceCurrency,
            ];
        }

        $payload = [
            'price_amount' => $priceAmount,
            'price_currency' => $priceCurrency,
            'pay_currency' => $payCurrency,
            'order_id' => $orderId,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ];

        return $this->request('/invoice', 'POST', $payload);
    }

    public function getPaymentStatus(string $invoiceId): array
    {
        return $this->request('/invoice/' . urlencode($invoiceId));
    }

    private function request(string $endpoint, string $method = 'GET', array $payload = []): array
    {
        if (!$this->apiKey) {
            return [];
        }

        $url = $this->baseUrl . $endpoint;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        $headers = [
            'x-api-key: ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $response = curl_exec($ch);
        if ($response === false) {
            throw new \RuntimeException('NowPayments isteği başarısız: ' . curl_error($ch));
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode >= 400) {
            throw new \RuntimeException('NowPayments API hatası: ' . $response);
        }

        $data = json_decode($response, true);
        return $data ?: [];
    }
}
