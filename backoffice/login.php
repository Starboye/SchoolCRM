<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
app_session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . app_url('index.php'));
    exit;
}

$username = trim((string)($_POST['username'] ?? ''));
$password = (string)($_POST['password'] ?? '');
$usertype = (int)($_POST['userType'] ?? -1);

if ($username === '' || $password === '' || !in_array($usertype, [0, 1, 2], true)) {
    header('Location: ' . app_url('index.php') . '?error=invalid');
    exit;
}

$conn = db_mysqli();
$ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
$ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 250);

$stmt = $conn->prepare('SELECT id, name, password, access FROM user_login WHERE name = ? LIMIT 1');
$stmt->bind_param('s', $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

function login_audit(mysqli $conn, ?string $userId, string $username, string $status, string $ip, string $ua): void {
    if (!db_table_exists($conn, 'login_audit')) {
        return;
    }
    $sql = 'INSERT INTO login_audit (user_id, username, status, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)';
    $stmt = $conn->prepare($sql);
    $uid = $userId;
    $stmt->bind_param('sssss', $uid, $username, $status, $ip, $ua);
    $stmt->execute();
    $stmt->close();
}

if (!$user || !app_password_verify($password, (string)$user['password'])) {
    login_audit($conn, null, $username, 'failed', $ip, $ua);
    header('Location: ' . app_url('index.php') . '?error=credentials');
    exit;
}

if ((int)$user['access'] !== $usertype) {
    login_audit($conn, (string)$user['id'], $username, 'failed', $ip, $ua);
    header('Location: ' . app_url('index.php') . '?error=role');
    exit;
}

if (!password_is_hashed((string)$user['password'])) {
    $newHash = app_password_hash($password);
    $up = $conn->prepare('UPDATE user_login SET password = ? WHERE id = ?');
    $up->bind_param('ss', $newHash, $user['id']);
    $up->execute();
    $up->close();
}

login_audit($conn, (string)$user['id'], $username, 'success', $ip, $ua);
session_regenerate_id(true);
establish_user_session($user);
$_SESSION['portal_boot'] = true;
header('Location: ' . role_home((int)$user['access']));
exit;
