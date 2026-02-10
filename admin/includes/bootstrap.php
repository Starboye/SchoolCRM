<?php
session_start();

if (!isset($_SESSION['access'], $_SESSION['id'], $_SESSION['name'])) {
    header('Location: ../index.php');
    exit;
}

$access = (int)$_SESSION['access'];
if (!in_array($access, [2, 3], true)) {
    http_response_code(403);
    die('Unauthorized access');
}

$db = mysqli_connect('localhost', 'root', '', 'asimos');
if (!$db) {
    die('Database connection failed: ' . mysqli_connect_error());
}

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
