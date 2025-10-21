<?php

declare(strict_types=1);

namespace App\I18n;

use RuntimeException;
use Throwable;

class Translator
{
    /** @var string */
    protected $cachePath;

    /** @var string|null */
    protected $apiKey;

    /** @var string */
    protected $fallbackLocale;

    /** @var string */
    protected $locale = 'en';

    public function __construct(string $cachePath, ?string $apiKey, string $fallbackLocale)
    {
        $this->cachePath = rtrim($cachePath, '/');
        $this->apiKey = $apiKey ?: null;
        $this->fallbackLocale = $fallbackLocale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function get(string $key, array $replace = [], ?string $locale = null): string
    {
        $locale = $locale ?: $this->locale;
        $lines = $this->loadLocale($locale);

        $value = $lines[$key] ?? null;
        if ($value === null && $locale !== $this->fallbackLocale) {
            $lines = $this->loadLocale($this->fallbackLocale);
            $value = $lines[$key] ?? $key;
        }

        $value = $value ?? $key;

        foreach ($replace as $k => $v) {
            $value = str_replace(':' . $k, (string) $v, $value);
        }

        return $value;
    }

    protected function loadLocale(string $locale): array
    {
        static $cache = [];
        if (isset($cache[$locale])) {
            return $cache[$locale];
        }

        $file = BASE_PATH . '/app/lang/' . $locale . '.php';
        if (!is_file($file)) {
            return $cache[$locale] = [];
        }

        $lines = include $file;
        if (!is_array($lines)) {
            throw new RuntimeException('Invalid language file for locale ' . $locale);
        }

        return $cache[$locale] = $lines;
    }

    public function translateRemote(string $text, string $targetLocale, string $sourceLocale = 'en'): ?string
    {
        if (!$this->apiKey || trim($text) === '') {
            return null;
        }

        $hash = hash('sha256', $sourceLocale . '|' . $targetLocale . '|' . $text);
        $cached = $this->readCache($hash);
        if ($cached !== null) {
            return $cached;
        }

        $payload = http_build_query([
            'auth_key' => $this->apiKey,
            'text'     => $text,
            'target_lang' => strtoupper($targetLocale),
            'source_lang' => strtoupper($sourceLocale),
        ]);

        $ch = curl_init('https://api-free.deepl.com/v2/translate');
        if ($ch === false) {
            return null;
        }

        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT        => 15,
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            curl_close($ch);
            return null;
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status !== 200) {
            return null;
        }

        $json = json_decode($response, true);
        $translation = $json['translations'][0]['text'] ?? null;
        if ($translation) {
            $this->writeCache($hash, $translation);
        }

        return $translation;
    }

    protected function readCache(string $hash): ?string
    {
        $file = $this->cachePath . '/' . $hash . '.txt';
        if (!is_file($file)) {
            return null;
        }

        return file_get_contents($file) ?: null;
    }

    protected function writeCache(string $hash, string $translation): void
    {
        $dir = $this->cachePath;
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        try {
            file_put_contents($dir . '/' . $hash . '.txt', $translation);
        } catch (Throwable $e) {
            // Ignore cache write errors silently.
        }
    }
}
