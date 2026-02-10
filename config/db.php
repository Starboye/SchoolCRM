<?php
// Minimal PDO wrapper. Update credentials.
declare(strict_types=1);
if (!defined('DB_DSN')) {
  define('DB_DSN', 'mysql:host=127.0.0.1;dbname=asimos;charset=utf8mb4');
  define('DB_USER', 'root');
  define('DB_PASS', '');
}
function db(): PDO {
  static $pdo = null;
  if ($pdo === null) {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
  }
  return $pdo;
}
