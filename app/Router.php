<?php

declare(strict_types=1);

class Router
{
    /** @var array<string, array> */
    protected $routes = [];

    public function add(string $method, string $uri, callable $action, ?string $name = null): void
    {
        $method = strtoupper($method);
        $this->routes[$method][$uri] = ['action' => $action, 'name' => $name];
    }

    public function get(string $uri, callable $action, ?string $name = null): void
    {
        $this->add('GET', $uri, $action, $name);
    }

    public function post(string $uri, callable $action, ?string $name = null): void
    {
        $this->add('POST', $uri, $action, $name);
    }

    public function dispatch(): void
    {
        $uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $route => $config) {
            $pattern = preg_replace('#\{([^}]+)\}#', '(?P<$1>[^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                view_share(['currentRoute' => $config['name'] ?? null]);
                echo call_user_func_array($config['action'], $params);
                return;
            }
        }

        abort(404, __('errors.not_found'));
    }
}
