<?php
declare(strict_types=1);
namespace App\Models;
require_once __DIR__.'/../db.php';
use PDO;

class Riddle {
  public static function paginate(array $opts): array {
    $pdo = db(); $w=['r.status = "published"']; $p=[];
    if (!empty($opts['difficulty'])) { $w[]='r.difficulty=?'; $p[]=$opts['difficulty']; }
    if (!empty($opts['length']))     { $w[]='r.length=?';     $p[]=$opts['length']; }
    if (!empty($opts['category']))   { $w[]='EXISTS(SELECT 1 FROM riddle_category rc JOIN categories c ON c.id=rc.category_id WHERE rc.riddle_id=r.id AND c.slug=?)'; $p[]=$opts['category']; }
    $where = 'WHERE '.implode(' AND ',$w);
    $sort = ($opts['sort']??'pop')==='new' ? 'r.published_at DESC' : '(r.up_votes - r.down_votes) DESC, r.views DESC, r.id DESC';
    $page = max(1,(int)($opts['page']??1)); $per = min(60, max(10,(int)($opts['per_page']??20)));
    $off  = ($page-1)*$per;

    $st=$pdo->prepare("SELECT COUNT(*) AS c FROM riddles r $where"); $st->execute($p);
    $total=(int)$st->fetchColumn();
    $stmt=$pdo->prepare("SELECT r.* FROM riddles r $where ORDER BY $sort LIMIT $per OFFSET $off");
    $stmt->execute($p);
    $items=$stmt->fetchAll(PDO::FETCH_ASSOC);
    return ['items'=>$items,'total'=>$total,'page'=>$page,'per'=>$per];
  }

  public static function findBySlug(string $slug):?array {
    $pdo=db(); $st=$pdo->prepare("SELECT * FROM riddles WHERE slug=? AND status='published'"); $st->execute([$slug]);
    $row=$st->fetch(PDO::FETCH_ASSOC); return $row?:null;
  }

  public static function incrementViews(int $id): void {
    $st = db()->prepare('UPDATE riddles SET views = views + 1 WHERE id = ?');
    $st->execute([$id]);
  }
}
