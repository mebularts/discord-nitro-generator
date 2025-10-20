
<?php
declare(strict_types=1);
namespace App\Controllers;
require_once __DIR__.'/../Models/Page.php';
require_once __DIR__.'/../helpers.php';
use App\Models\Page;

class StaticController {
  public function page($slug){
    $p = Page::findBySlug($slug);
    if(!$p){ http_response_code(404); exit('Not Found'); }
    view('static/page.php', ['p'=>$p]);
  }
}
