<?php
declare(strict_types=1);
namespace App\Models;
require_once __DIR__.'/../db.php';
use PDO;

class Page {
  public static function findBySlug(string $slug):?array{
    $st=db()->prepare("SELECT * FROM pages WHERE slug=?"); $st->execute([$slug]);
    $r=$st->fetch(PDO::FETCH_ASSOC); return $r?:null;
  }
}
