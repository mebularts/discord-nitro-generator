<?php
namespace App\Services\Sms;

interface SmsProviderInterface
{
    public function getServices(): array;

    public function getCountriesForService(string $serviceId): array;

    public function requestNumber(string $serviceId, string $countryCode): array;

    public function getSmsCode(string $orderId): ?string;

    public function cancelOrder(string $orderId): void;
}
