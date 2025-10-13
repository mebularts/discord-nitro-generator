<?php
namespace App\Support;

use App\Models\SettingRepository;

class Settings
{
    public static function hydrateConfig(): void
    {
        $repo = new SettingRepository();
        $settings = $repo->all();

        foreach ($settings as $name => $value) {
            Config::set($name, self::decodeValue($value));
        }
    }

    private static function decodeValue(string $value)
    {
        $decoded = json_decode($value, true);
        return $decoded === null && json_last_error() !== JSON_ERROR_NONE ? $value : $decoded;
    }
}
