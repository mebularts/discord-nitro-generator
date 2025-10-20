<?php
declare(strict_types=1);
namespace App\Controllers;
require_once __DIR__.'/../helpers.php';
require_once __DIR__.'/../db.php';

class ApiController {
  public function vote(){
    $in=json_decode(file_get_contents('php://input'), true);
    $id=(int)($in['id']??0); $dir=((int)($in['dir']??0))===1?1:-1;
    if(!$id) {
      http_response_code(422);
      return json_response(['ok'=>false,'error'=>'invalid id']);
    }

    $pdo=db(); $pdo->beginTransaction();
    $ip = @inet_pton($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
    if($ip === false){ $ip = null; }
    $ua = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
    $st=$pdo->prepare("INSERT IGNORE INTO votes(riddle_id,direction,ip,ua_hash) VALUES(?,?,?,?)");
    $st->execute([$id,$dir,$ip,$ua]);
    if($st->rowCount()>0){
      $col = $dir===1 ? 'up_votes' : 'down_votes';
      $pdo->prepare("UPDATE riddles SET $col=$col+1 WHERE id=?")->execute([$id]);
    }
    $stmt=$pdo->prepare('SELECT up_votes,down_votes FROM riddles WHERE id=?');
    $stmt->execute([$id]);
    $row=$stmt->fetch();
    if(!$row){
      $pdo->rollBack();
      http_response_code(404);
      return json_response(['ok'=>false,'error'=>'not found']);
    }
    $pdo->commit();
    $total=max(1, (int)$row['up_votes']+(int)$row['down_votes']);
    $percent=round(100*(int)$row['up_votes']/$total,2);
    return json_response(['ok'=>true,'percent'=>$percent]);
  }
}
