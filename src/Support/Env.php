<?php
namespace App\Support;

class Env
{
    public static function load(string $path, bool $overload = false): void
    {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }

            if (strpos($line, 'export ') === 0) {
                $line = trim(substr($line, 7));
            }

            if (strpos($line, '=') === false) {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = self::sanitizeValue($value);

            if ($name === '') {
                continue;
            }

            if (!$overload && getenv($name) !== false) {
                continue;
            }

            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }

    private static function sanitizeValue(string $value): string
    {
        $value = trim($value);

        $hashPos = self::findUnescapedHash($value);
        if ($hashPos !== false) {
            $value = substr($value, 0, $hashPos);
        }

        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if ($value[0] === '"' && substr($value, -1) === '"') {
            $value = substr($value, 1, -1);
            $value = str_replace('\\"', '"', $value);
        } elseif ($value[0] === "'" && substr($value, -1) === "'") {
            $value = substr($value, 1, -1);
        }

        return str_replace('\\#', '#', $value);
    }

    private static function findUnescapedHash(string $value)
    {
        $length = strlen($value);
        for ($i = 0; $i < $length; $i++) {
            if ($value[$i] === '#') {
                $escaped = $i > 0 && $value[$i - 1] === '\\';
                if (!$escaped) {
                    return $i;
                }
            }
        }

        return false;
    }
}
