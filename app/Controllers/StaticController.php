<?php
declare(strict_types=1);
namespace App\Controllers;
require_once __DIR__.'/../Models/Page.php';
require_once __DIR__.'/../helpers.php';
use App\Models\Page;

class StaticController {
  public function page($slug=null){
    if($slug === null){
      $slug = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
    }
    $p = Page::findBySlug($slug);
    if(!$p){ http_response_code(404); exit('Not Found'); }
    view('static/page.php', ['p'=>$p]);
  }
}
