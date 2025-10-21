<?php
declare(strict_types=1);

namespace App\I18n;

use function app_log;
use function app_path;
use function db;
use function setting;
use function storage_path;

class Translator
{
    private static array $catalogue = [];

    public static function phrase(string $key, string $locale, string $fallback): string
    {
        $locale = strtolower($locale);
        $catalogue = self::loadLocale($locale);
        if (isset($catalogue[$key])) {
            return $catalogue[$key];
        }

        $baseLocale = 'en';
        $baseCatalogue = self::loadLocale($baseLocale);
        $base = $baseCatalogue[$key] ?? $fallback;

        if ($locale === $baseLocale) {
            return $base;
        }

        $translated = self::translate($base, strtoupper($locale), strtoupper($baseLocale));
        if (!is_string($translated) || trim($translated) === '') {
            return $base;
        }

        self::store($locale, $key, $translated);
        return $translated;
    }

    public static function translate(string $text, string $targetLang, ?string $sourceLang = null): string
    {
        $text = (string) $text;
        $targetLang = strtoupper(trim($targetLang));
        $sourceLang = $sourceLang ? strtoupper(trim($sourceLang)) : null;

        if ($text === '' || $targetLang === '') {
            return $text;
        }

        $hash = hash('sha256', $text . '|' . $targetLang . '|' . ($sourceLang ?: 'auto'));
        $pdo = db();
        $stmt = $pdo->prepare('SELECT translated_text FROM translations WHERE hash = ? LIMIT 1');
        $stmt->execute([$hash]);
        $cached = $stmt->fetchColumn();
        if ($cached !== false) {
            return (string) $cached;
        }

        $apiKey = getenv('DEEPL_API_KEY') ?: setting('deepl.api_key');
        if (!$apiKey) {
            return $text;
        }

        $url = 'https://api-free.deepl.com/v2/translate';
        $data = [
            'text'        => $text,
            'target_lang' => $targetLang,
        ];
        if ($sourceLang) {
            $data['source_lang'] = $sourceLang;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: DeepL-Auth-Key ' . $apiKey]);
        $res = curl_exec($ch);
        if ($res === false) {
            app_log('DeepL request failed', ['error' => curl_error($ch)]);
            return $text;
        }

        $json = json_decode($res, true);
        if (isset($json['message'])) {
            app_log('DeepL error', ['response' => $json]);
        }
        if (isset($json['translations'][0]['text'])) {
            $translated = $json['translations'][0]['text'];
            $insert = $pdo->prepare('INSERT INTO translations(hash, source_text, translated_text, source_lang, target_lang) VALUES(?,?,?,?,?)');
            $insert->execute([$hash, $text, $translated, $sourceLang, $targetLang]);
            return $translated;
        }

        return $text;
    }

    public static function flush(?string $locale = null): void
    {
        if ($locale === null) {
            self::$catalogue = [];
            return;
        }
        unset(self::$catalogue[strtolower($locale)]);
    }

    private static function loadLocale(string $locale): array
    {
        if (!isset(self::$catalogue[$locale])) {
            $paths = [
                app_path('lang/' . $locale . '.php'),
                storage_path('cache/lang-' . $locale . '.php'),
            ];
            $dictionary = [];
            foreach ($paths as $path) {
                if (is_file($path)) {
                    $data = include $path;
                    if (is_array($data)) {
                        $dictionary = $dictionary + $data;
                    }
                }
            }
            self::$catalogue[$locale] = $dictionary;
        }
        return self::$catalogue[$locale];
    }

    private static function store(string $locale, string $key, string $value): void
    {
        $locale = strtolower($locale);
        $catalogue = self::loadLocale($locale);
        $catalogue[$key] = $value;
        self::$catalogue[$locale] = $catalogue;

        $path = storage_path('cache/lang-' . $locale . '.php');
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $export = var_export($catalogue, true);
        $content = "<?php\nreturn " . $export . ';';
        @file_put_contents($path, $content);
    }
}
