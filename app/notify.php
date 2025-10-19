<?php
declare(strict_types=1);
require_once __DIR__.'/helpers.php';
function settings_get(PDO $pdo, string $key, $default=null){
  $s=$pdo->prepare("SELECT value FROM settings WHERE `key`=?"); $s->execute([$key]);
  $v=$s->fetchColumn(); if($v===false){ return $default; }
  $json=json_decode((string)$v,true); return $json===null? $v : $json;
}
function settings_set(PDO $pdo, string $key, $val): void{
  $v=(is_array($val)||is_object($val)) ? json_encode($val, JSON_UNESCAPED_UNICODE) : (string)$val;
  $pdo->prepare("INSERT INTO settings(`key`,`value`) VALUES(?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)")->execute([$key,$v]);
}
function send_email(PDO $pdo, string $to, string $subject, string $html): bool{
  $smtp  = settings_get($pdo,'smtp',[]); $from  = (!empty($smtp['from'])) ? $smtp['from'] : 'noreply@example.com';
  $headers  = "MIME-Version: 1.0\r\n"."Content-type: text/html; charset=utf-8\r\n"."From: ".$from."\r\n";
  return @mail($to,$subject,$html,$headers);
}
function send_sms(PDO $pdo, string $to, string $message): array{
  $sms=settings_get($pdo,'sms',[]); $provider=$sms['provider']??'netgsm'; $username=$sms['username']??''; $password=$sms['password']??''; $header=$sms['header']??'';
  $result=['ok'=>false,'provider'=>$provider,'response'=>null];
  if($provider==='netgsm'){ $url='https://api.netgsm.com.tr/sms/send/get/'; $params=['usercode'=>$username,'password'=>$password,'gsmno'=>$to,'message'=>$message,'msgheader'=>$header,'dil'=>'TR']; $resp=@file_get_contents($url.'?'.http_build_query($params)); $result['response']=$resp; $result['ok']=($resp!==false); }
  elseif($provider==='iletimerkezi'){ $url='https://api.iletimerkezi.com/v1/send-sms'; $payload=['request'=>['sender'=>$header,'messages'=>['mb'=>[['msg'=>$message,'phone'=>$to]]]],'auth'=>['username'=>$username,'password'=>$password]]; $ch=curl_init($url); curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_HTTPHEADER=>['Content-Type: application/json'],CURLOPT_POSTFIELDS=>json_encode($payload, JSON_UNESCAPED_UNICODE)]); $resp=curl_exec($ch); curl_close($ch); $result['response']=$resp; $result['ok']=($resp!==false && $resp!==null); }
  return $result;
}
