<?php
namespace App\Services\Sms;

use App\Support\Config;

class ProviderFactory
{
    public static function make(?string $provider = null): SmsProviderInterface
    {
        $provider = $provider ?: Config::get('providers.preferred', '5sim');

        return match ($provider) {
            'sms_activate' => new SmsActivateProvider(),
            default => new FiveSimProvider(),
        };
    }
}
