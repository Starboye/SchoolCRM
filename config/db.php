<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';

if (!defined('DB_DSN')) {
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);
    define('DB_DSN', $dsn);
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

function db_mysqli(): mysqli {
    static $mysqli = null;
    if ($mysqli === null) {
        mysqli_report(MYSQLI_REPORT_OFF);
        $mysqli = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if (!$mysqli) {
            http_response_code(500);
            die('Database connection failed.');
        }
        mysqli_set_charset($mysqli, 'utf8mb4');
    }
    return $mysqli;
}

function db_table_exists(mysqli $db, string $table): bool {
    $esc = mysqli_real_escape_string($db, $table);
    $res = mysqli_query($db, "SHOW TABLES LIKE '$esc'");
    return $res instanceof mysqli_result && mysqli_num_rows($res) > 0;
}
