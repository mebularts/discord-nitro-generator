<?php
declare(strict_types=1);
require_once __DIR__.'/../config/db.php';

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function redirect(string $u){ header('Location: '.$u); exit; }
function is_post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'; }

function csrf_token(){ if(empty($_SESSION['_csrf'])) $_SESSION['_csrf']=bin2hex(random_bytes(16)); return $_SESSION['_csrf']; }
function csrf_field(){ return '<input type="hidden" name="_csrf" value="'.h(csrf_token()).'">'; }
function csrf_check(){ if(($_POST['_csrf']??'')!==($_SESSION['_csrf']??'')) { http_response_code(419); exit('CSRF token mismatch'); } }

function set_flash($k,$v){ $_SESSION['_flash'][$k]=$v; }
function get_flash($k){ $v=$_SESSION['_flash'][$k]??null; unset($_SESSION['_flash'][$k]); return $v; }

function must_login(){ if(empty($_SESSION['admin'])) redirect('/admin/login.php'); }
function q(PDO $pdo, string $sql, array $args=[]){ $s=$pdo->prepare($sql); $s->execute($args); return $s; }

function t(string $k){
  $langFile = __DIR__.'/../storage/lang/strings.json';
  static $dict=null;
  if($dict===null){
    if(!file_exists($langFile)){
      @mkdir(dirname($langFile),0777,true);
      file_put_contents($langFile, json_encode([
        'default_lang'=>'tr','langs'=>['tr'],
        'strings'=>['tr'=>['title'=>'Randevu Sistemi','btn.next'=>'Devam et','btn.prev'=>'Geri dön','book.info'=>'Genel bilgiler','book.date'=>'Randevu tarihi','book.time'=>'Randevu saati','confirm.provider'=>'Randevu veren','confirm.date'=>'Randevu tarihi','confirm.time'=>'Randevu saati','success.title'=>'Randevunuz Oluşturuldu!']]
      ], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    }
    $dict=json_decode((string)file_get_contents($langFile),true);
  }
  $lang=$dict['default_lang']??'tr'; return $dict['strings'][$lang][$k]??$k;
}

function format_month_year(DateTime $dt, string $locale='tr_TR'): string {
  if (class_exists('IntlDateFormatter')) {
    $fmt = new IntlDateFormatter($locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, $dt->getTimezone()->getName(), IntlDateFormatter::GREGORIAN, 'LLLL yyyy');
    $s = $fmt->format($dt); if($s !== false) return $s;
  }
  static $ay = ['01'=>'Ocak','02'=>'Şubat','03'=>'Mart','04'=>'Nisan','05'=>'Mayıs','06'=>'Haziran','07'=>'Temmuz','08'=>'Ağustos','09'=>'Eylül','10'=>'Ekim','11'=>'Kasım','12'=>'Aralık'];
  $m=$dt->format('m'); return ($ay[$m] ?? $dt->format('F')).' '.$dt->format('Y');
}
