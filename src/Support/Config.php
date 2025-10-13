<?php
namespace App\Support;

class Config
{
    /** @var array */
    private static $config = [];

    public static function load(array $config): void
    {
        self::$config = $config;
    }

    public static function get(string $key, $default = null)
    {
        $segments = explode('.', $key);
        $value = self::$config;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    public static function set(string $key, $value): void
    {
        $segments = explode('.', $key);
        $config =& self::$config;

        foreach ($segments as $segment) {
            if (!isset($config[$segment]) || !is_array($config[$segment])) {
                $config[$segment] = [];
            }

            $config =& $config[$segment];
        }

        $config = $value;
    }

    public static function all(): array
    {
        return self::$config;
    }
}
