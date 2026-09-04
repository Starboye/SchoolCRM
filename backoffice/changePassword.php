<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
app_session_start();

function change_password_redirect(string $message, string $path): void {
    $url = app_url($path);
    echo '<script>alert(' . json_encode($message) . '); window.location.href = ' . json_encode($url) . ';</script>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['id'], $_SESSION['name'])) {
    header('Location: ' . app_url('index.php'));
    exit;
}

$sessionId       = (string)$_SESSION['id'];
$username        = (string)$_SESSION['name'];
$postedId        = trim((string)($_POST['id'] ?? ''));
$currentPassword = (string)($_POST['currentPassword'] ?? '');
$newPassword     = (string)($_POST['newPassword'] ?? '');
$confirmPassword = (string)($_POST['confirmPassword'] ?? '');
$profilePath     = 'users-profile.php';

if ($newPassword === '' || $currentPassword === '' || $confirmPassword === '') {
    change_password_redirect('All fields are required.', $profilePath);
}

if ($newPassword !== $confirmPassword) {
    change_password_redirect('New password and confirm password do not match.', $profilePath);
}

if (strlen($newPassword) < 6) {
    change_password_redirect('New password should be at least 6 characters long.', $profilePath);
}

if ($postedId !== '' && $postedId !== $sessionId) {
    change_password_redirect('User not found or session mismatch.', $profilePath);
}

$conn = db_mysqli();
$stmt = $conn->prepare('SELECT password FROM user_login WHERE id = ? AND name = ? LIMIT 1');
$stmt->bind_param('ss', $sessionId, $username);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    change_password_redirect('User not found or session mismatch.', $profilePath);
}

if (!app_password_verify($currentPassword, (string)$row['password'])) {
    change_password_redirect('Current password is incorrect.', $profilePath);
}

$newHash = app_password_hash($newPassword);
$update = $conn->prepare('UPDATE user_login SET password = ? WHERE id = ? AND name = ?');
$update->bind_param('sss', $newHash, $sessionId, $username);

if ($update->execute()) {
    change_password_redirect('Password updated successfully.', $profilePath);
}

change_password_redirect('Error updating password. Please try again.', $profilePath);
