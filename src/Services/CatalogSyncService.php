<?php
namespace App\Services;

use App\Models\CountryRepository;
use App\Models\ServiceRepository;
use App\Models\SettingRepository;
use App\Services\Sms\ProviderFactory;
use App\Services\Sms\SmsProviderInterface;
use App\Support\Config;

class CatalogSyncService
{
    /** @var ServiceRepository */
    private $services;
    /** @var CountryRepository */
    private $countries;
    /** @var SmsProviderInterface */
    private $provider;
    /** @var SettingRepository */
    private $settings;

    public function __construct(?SmsProviderInterface $provider = null)
    {
        $this->services = new ServiceRepository();
        $this->countries = new CountryRepository();
        $this->provider = $provider ?: ProviderFactory::make();
        $this->settings = new SettingRepository();
    }

    public function syncAll(): array
    {
        $services = $this->provider->getServices();
        $synced = [];
        foreach ($services as $service) {
            $normalized = $this->normalizeService($service);
            $model = $this->services->upsert($normalized);
            $synced[] = $model;
            $this->syncCountriesForService($model);
        }

        if ($synced) {
            $timestamp = date('c');
            $this->settings->set('catalog.last_sync', $timestamp);
            Config::set('catalog.last_sync', $timestamp);
        }

        return $synced;
    }

    public function syncCountriesForService(array $service): array
    {
        $countries = $this->provider->getCountriesForService($service['provider_service_id']);
        $synced = [];
        foreach ($countries as $country) {
            $normalized = $this->normalizeCountry($country);
            if (!$normalized) {
                continue;
            }
            $countryModel = $this->countries->upsert($normalized);
            $serviceCountryData = [
                'stock' => (int) ($country['stock'] ?? 0),
                'price' => (float) ($country['price'] ?? $service['base_price']),
                'provider_country_code' => $normalized['provider_code'],
            ];
            $this->countries->syncServiceCountry((int) $service['id'], (int) $countryModel['id'], $serviceCountryData);
            $synced[] = $countryModel;
        }

        return $synced;
    }

    private function normalizeService(array $service): array
    {
        $providerKey = get_class($this->provider);
        $id = (string) ($service['id'] ?? $service['provider_service_id'] ?? $service['slug'] ?? $service['name']);
        $price = (float) ($service['base_price'] ?? $service['price'] ?? 0);
        if ($price <= 0) {
            $price = (float) Config::get('catalog.default_price', 10.0);
        }

        return [
            'provider_service_id' => $id,
            'name' => (string) ($service['name'] ?? ucfirst($id)),
            'description' => $service['description'] ?? null,
            'provider' => $providerKey,
            'base_price' => $price,
            'popular' => !empty($service['popular']) ? 1 : 0,
            'provider_category' => $service['category'] ?? $service['provider_category'] ?? null,
        ];
    }

    private function normalizeCountry(array $country): ?array
    {
        $code = (string) ($country['provider_country_code'] ?? $country['code'] ?? $country['id'] ?? '');
        if ($code === '') {
            return null;
        }

        return [
            'code' => strtoupper((string) ($country['iso'] ?? $country['code'] ?? substr($code, 0, 2))),
            'name' => (string) ($country['name'] ?? $country['title'] ?? $code),
            'provider_code' => $code,
            'dial_prefix' => $country['dial_prefix'] ?? $country['prefix'] ?? null,
        ];
    }
}
