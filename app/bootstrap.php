<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/Router.php';

// .env yükle
$envFile = dirname(__DIR__).'/.env';
if (file_exists($envFile)) {
  $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    $pos = strpos($line, '=');
    if ($pos === false) continue;
    $k = trim(substr($line, 0, $pos));
    $v = trim(substr($line, $pos+1));
    if (!getenv($k)) putenv("$k=$v");
  }
}

// Hata gösterimi
$env = getenv('APP_ENV') ?: 'production';
if ($env !== 'production') {
  ini_set('display_errors', '1');
  error_reporting(E_ALL);
} else {
  ini_set('display_errors', '0');
  error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
}
