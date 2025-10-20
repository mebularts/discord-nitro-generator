
<?php
declare(strict_types=1);

function db(): PDO {
  static $pdo = null;
  if ($pdo) return $pdo;
  $host = getenv('DB_HOST') ?: '127.0.0.1';
  $port = getenv('DB_PORT') ?: '3306';
  $db   = getenv('DB_NAME') ?: 'solveorclone';
  $user = getenv('DB_USER') ?: 'root';
  $pass = getenv('DB_PASS') ?: '';
  $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
  $opt = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ];
  try {
    $pdo = new PDO($dsn, $user, $pass, $opt);
  } catch (Throwable $e) {
    http_response_code(500);
    exit('DB connection failed: ' . $e->getMessage());
  }
  return $pdo;
}
