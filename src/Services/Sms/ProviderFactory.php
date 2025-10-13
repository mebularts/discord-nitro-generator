<?php
namespace App\Services\Sms;

use App\Support\Config;

class ProviderFactory
{
    public static function make(?string $provider = null): SmsProviderInterface
    {
        $provider = $provider ?: Config::get('providers.preferred', '5sim');

        switch ($provider) {
            case 'sms_activate':
                return new SmsActivateProvider();
            default:
                return new FiveSimProvider();
        }
    }

    public static function fromClass(?string $providerClass): SmsProviderInterface
    {
        switch ($providerClass) {
            case SmsActivateProvider::class:
                return new SmsActivateProvider();
            case FiveSimProvider::class:
                return new FiveSimProvider();
            default:
                return self::make();
        }
    }
}
