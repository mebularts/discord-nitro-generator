
<?php
declare(strict_types=1);

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function base_url($path=''){
  $url = getenv('APP_URL') ?: '';
  if ($url && substr($url,-1) === '/') $url = rtrim($url,'/');
  return $url . $path;
}

function view($file, $vars=[]) {
  extract($vars);
  require __DIR__ . '/Views/' . $file;
}

function json_response($arr){ header('Content-Type: application/json; charset=utf-8'); echo json_encode($arr); exit; }

function csrf_token(){
  if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
  if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
  return $_SESSION['csrf'];
}
function csrf_check(){
  if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
  $ok = isset($_POST['csrf']) && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
  if(!$ok){ http_response_code(400); exit('Bad CSRF'); }
}

function paginator($page,$per,$total){
  $pages = max(1, (int)ceil($total / max(1,$per)));
  if ($pages<=1) return;
  echo '<nav class="tw-flex tw-gap-2 tw-my-6">';
  for($i=1;$i<=$pages;$i++){
    $cls = $i==$page ? 'tw-bg-indigo-600 tw-text-white' : 'tw-bg-white tw-text-gray-700';
    $q = $_GET; $q['page']=$i; $qs = http_build_query($q);
    echo '<a class="tw-px-3 tw-py-1 tw-rounded tw-border '+$cls+'" href="?'.$qs.'">'.$i.'</a>';
  }
  echo '</nav>';
}
