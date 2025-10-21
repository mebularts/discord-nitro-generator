<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Controllers\Controller;
use Closure;
use InvalidArgumentException;

final class Router
{
    /** @var array<string, array<int, array{pattern: string, handler: callable|string, middleware: array, parameters: array}>> */
    private array $routes = [];

    public function add(string $method, string $path, $handler, array $middleware = []): void
    {
        $method = strtoupper($method);
        $pattern = $this->compilePattern($path);
        $this->routes[$method][] = [
            'pattern' => $pattern['regex'],
            'handler' => $handler,
            'middleware' => $middleware,
            'parameters' => $pattern['params'],
        ];
    }

    public function get(string $path, $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path = $request->path();
        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['pattern'], $path, $matches)) {
                $params = [];
                foreach ($route['parameters'] as $index => $name) {
                    $params[$name] = $matches[$index + 1] ?? null;
                }
                $handler = $route['handler'];
                $middleware = $route['middleware'];
                $runner = $this->createRunner($handler, $request, $params);
                foreach (array_reverse($middleware) as $mw) {
                    $next = $runner;
                    $runner = static function () use ($mw, $request, $next) {
                        return $mw($request, $next);
                    };
                }
                $runner();
                return;
            }
        }

        http_response_code(404);
        echo '404';
    }

    private function compilePattern(string $path): array
    {
        $params = [];
        $pattern = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)}/', static function (array $matches) use (&$params) {
            $params[] = $matches[1];
            return '([\\w-]+)';
        }, rtrim($path, '/') ?: '/');
        $regex = '#^' . $pattern . '$#';
        return ['regex' => $regex, 'params' => $params];
    }

    private function createRunner($handler, Request $request, array $params): callable
    {
        if ($handler instanceof Closure) {
            return static function () use ($handler, $request, $params) {
                $handler($request, ...array_values($params));
            };
        }

        if (is_array($handler) && count($handler) === 2) {
            [$controller, $method] = $handler;
            if (is_string($controller)) {
                $controller = new $controller();
            }
            if (!$controller instanceof Controller) {
                throw new InvalidArgumentException('Controller must extend base Controller.');
            }

            return static function () use ($controller, $method, $request, $params) {
                $controller->$method($request, ...array_values($params));
            };
        }

        if (is_string($handler)) {
            if (!str_contains($handler, '@')) {
                throw new InvalidArgumentException('Invalid handler string.');
            }
            [$controllerClass, $method] = explode('@', $handler);
            $controller = new $controllerClass();
            if (!$controller instanceof Controller) {
                throw new InvalidArgumentException('Controller must extend base Controller.');
            }

            return static function () use ($controller, $method, $request, $params) {
                $controller->$method($request, ...array_values($params));
            };
        }

        throw new InvalidArgumentException('Unknown route handler.');
    }
}
