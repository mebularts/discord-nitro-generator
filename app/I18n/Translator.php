<?php
declare(strict_types=1);
namespace App\I18n;

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

class Translator {
  public static function translate($text, $targetLang, $sourceLang=null){
    $text = (string)$text;
    $targetLang = strtoupper((string)$targetLang);
    $sourceLang = $sourceLang ? strtoupper((string)$sourceLang) : null;
    if($text === '' || $targetLang === ''){
      return $text;
    }

    $hash = hash('sha256', $text.'|'.$targetLang.'|'.($sourceLang ?: 'auto'));
    $pdo = db();
    $stmt = $pdo->prepare('SELECT translated_text FROM translations WHERE hash = ? LIMIT 1');
    $stmt->execute([$hash]);
    $cached = $stmt->fetchColumn();
    if($cached !== false){
      return $cached;
    }

    $apiKey = getenv('DEEPL_API_KEY') ?: setting('deepl.api_key');
    if(!$apiKey){
      return $text;
    }
    $url = 'https://api-free.deepl.com/v2/translate';
    $data = [
      'text' => $text,
      'target_lang' => $targetLang,
    ];
    if($sourceLang){ $data['source_lang'] = $sourceLang; }
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: DeepL-Auth-Key '.$apiKey]);
    $res = curl_exec($ch);
    if($res===false){
      app_log('DeepL request failed', ['error' => curl_error($ch)]);
      return $text;
    }
    $json = json_decode($res, true);
    if(isset($json['message'])){
      app_log('DeepL error', ['response' => $json]);
    }
    if(isset($json['translations'][0]['text'])){
      $translated = $json['translations'][0]['text'];
      $insert = $pdo->prepare('INSERT INTO translations(hash, source_text, translated_text, source_lang, target_lang) VALUES(?,?,?,?,?)');
      $insert->execute([$hash, $text, $translated, $sourceLang, $targetLang]);
      return $translated;
    }
    return $text;
  }
}
