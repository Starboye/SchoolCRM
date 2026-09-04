<?php
require_once __DIR__ . '/../../config/db.php';
app_session_start();

if (!isset($_SESSION['access'], $_SESSION['id'], $_SESSION['name'])) {
    header('Location: ' . app_url('index.php'));
    exit;
}

if ((int)$_SESSION['access'] !== 1) {
    http_response_code(403);
    die('Unauthorized Access');
}

$teacherId = $_SESSION['id'];
$teacherName = $_SESSION['name'];
