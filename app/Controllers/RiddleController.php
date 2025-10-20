<?php
declare(strict_types=1);
namespace App\Controllers;
require_once __DIR__.'/../Models/Riddle.php';
require_once __DIR__.'/../helpers.php';
use App\Models\Riddle;

class RiddleController {
  public function show($slug){
    $r = Riddle::findBySlug($slug);
    if(!$r){ http_response_code(404); exit('Not Found'); }
    Riddle::incrementViews((int)$r['id']);
    view('riddle/show.php', ['riddle'=>$r]);
  }
}
