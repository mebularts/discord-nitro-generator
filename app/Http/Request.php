<?php

declare(strict_types=1);

namespace App\Http;

use App\Support\Session;

final class Request
{
    private array $body;

    public function __construct()
    {
        $this->body = $this->parseBody();
    }

    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        return rtrim($path, '/') ?: '/';
    }

    public function input(string $key, $default = null)
    {
        return $this->body[$key] ?? $_GET[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($_GET, $this->body);
    }

    public function files(): array
    {
        return $_FILES;
    }

    public function bearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (stripos($header, 'Bearer ') === 0) {
            return substr($header, 7);
        }
        return null;
    }

    public function csrfToken(): string
    {
        return (string) ($this->body['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    }

    public function session(string $key, $default = null)
    {
        return Session::get($key, $default);
    }

    private function parseBody(): array
    {
        if ($this->method() === 'GET') {
            return [];
        }

        if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
            $input = file_get_contents('php://input') ?: '';
            if ($input === '') {
                return [];
            }
            $decoded = json_decode($input, true);
            return is_array($decoded) ? $decoded : [];
        }

        return $_POST;
    }
}
