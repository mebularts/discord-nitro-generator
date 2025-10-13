<?php
namespace App\Telegram;

use App\Support\Config;

class TelegramClient
{
    private string $apiUrl;

    public function __construct(?string $botToken = null)
    {
        $token = $botToken ?: Config::get('telegram.bot_token');
        $this->apiUrl = 'https://api.telegram.org/bot' . $token . '/';
    }

    public function sendRequest(string $method, array $params = []): array
    {
        $ch = curl_init($this->apiUrl . $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

        $response = curl_exec($ch);
        if ($response === false) {
            throw new \RuntimeException('Telegram request failed: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);
        if ($httpCode >= 400 || !($data['ok'] ?? false)) {
            throw new \RuntimeException('Telegram API error: ' . $response);
        }

        return $data;
    }
}
