<?php
declare(strict_types=1);
namespace App;

class Router {
  private $routes = [];

  public function get($pattern, $handler){ $this->routes[] = ['GET',  $pattern, $handler]; }
  public function post($pattern, $handler){ $this->routes[] = ['POST', $pattern, $handler]; }

  public function dispatch(){
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

    foreach ($this->routes as $r) {
      [$m, $p, $h] = $r;
      if ($m !== $method) continue;

      // Route desenini regex'e çevir
      $regex = '#^' . $p . '$#u';

      if (preg_match($regex, $uri, $matches)) {
        array_shift($matches);

        if (is_string($h) && strpos($h, '@') !== false) {
          [$controller, $action] = explode('@', $h, 2);

          // DÜZELTME: FQCN doğru biçimde oluşturuluyor
          $fqcn = '\\App\\Controllers\\' . $controller;

          // Controller dosyasını yükle
          $path = __DIR__ . '/Controllers/' . $controller . '.php';
          if (is_file($path)) {
            require_once $path;
          }

          if (!class_exists($fqcn)) {
            http_response_code(500);
            exit("Controller sınıfı bulunamadı: {$fqcn}");
          }

          $obj = new $fqcn();
          if (!is_callable([$obj, $action])) {
            http_response_code(500);
            exit("Metot bulunamadı: {$fqcn}::{$action}()");
          }

          return call_user_func_array([$obj, $action], $matches);
        } elseif (is_callable($h)) {
          return call_user_func_array($h, $matches);
        } else {
          http_response_code(500);
          exit('Geçersiz route handler');
        }
      }
    }

    http_response_code(404);
    echo 'Not Found';
  }
}
