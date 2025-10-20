
<?php
declare(strict_types=1);
namespace App;
class Router {
  private $routes = [];
  public function get($pattern,$handler){ $this->routes[] = ['GET',$pattern,$handler]; }
  public function post($pattern,$handler){ $this->routes[] = ['POST',$pattern,$handler]; }
  public function dispatch(){
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    foreach($this->routes as $r){
      list($m,$p,$h) = $r;
      if($m!==$method) continue;
      $regex = "#^".$p."$#";
      if(preg_match($regex,$uri,$matches)){
        array_shift($matches);
        if (is_string($h) && strpos($h,'@')!==false){
          list($controller,$action)=explode('@',$h,2);
          $fqcn = '\App\Controllers\'. $controller;
          require_once __DIR__ . '/Controllers/'.$controller.'.php';
          $obj = new $fqcn();
          return call_user_func_array([$obj,$action], $matches);
        } elseif (is_callable($h)){
          return call_user_func_array($h, $matches);
        }
      }
    }
    http_response_code(404); echo "Not Found";
  }
}
