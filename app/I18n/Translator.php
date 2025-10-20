
<?php
declare(strict_types=1);
namespace App\I18n;

class Translator {
  public static function translate($text, $targetLang, $sourceLang=null){
    $apiKey = getenv('DEEPL_API_KEY');
    if(!$apiKey){ return $text; }
    $url = 'https://api-free.deepl.com/v2/translate';
    $data = [
      'text' => $text,
      'target_lang' => strtoupper($targetLang),
    ];
    if($sourceLang){ $data['source_lang'] = strtoupper($sourceLang); }
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: DeepL-Auth-Key '.$apiKey]);
    $res = curl_exec($ch);
    if($res===false){ return $text; }
    $json = json_decode($res, true);
    if(isset($json['translations'][0]['text'])){
      return $json['translations'][0]['text'];
    }
    return $text;
  }
}
