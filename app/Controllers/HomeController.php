<?php
declare(strict_types=1);
namespace App\Controllers;
require_once __DIR__.'/../Models/Riddle.php';
require_once __DIR__.'/../Models/Category.php';
require_once __DIR__.'/../helpers.php';

use App\Models\Riddle;
use App\Models\Category;

class HomeController {
  public function index(){
    $q=[
      'difficulty'=> $_GET['difficulty'] ?? null,
      'length'    => $_GET['length'] ?? null,
      'category'  => $_GET['category'] ?? null,
      'page'      => max(1,(int)($_GET['page'] ?? 1)),
      'per_page'  => 20,
      'sort'      => $_GET['sort'] ?? 'pop'
    ];
    $data = Riddle::paginate($q);
    $categories = Category::all();
    view('home.php', $data + ['filters'=>$q, 'categories'=>$categories]);
  }
  public function latest(){ $_GET['sort']='new'; return $this->index(); }
}
