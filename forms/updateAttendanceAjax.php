<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
app_session_start();

header('Content-Type: text/plain; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('method not allowed');
}

if (!isset($_SESSION['id'], $_SESSION['name'], $_SESSION['access']) || (int)$_SESSION['access'] !== 1) {
    http_response_code(403);
    exit('unauthorized');
}

$student_id = trim((string)($_POST['student_id'] ?? ''));
$status     = (string)($_POST['status'] ?? '');
$session    = (string)($_POST['session'] ?? '');
$date       = trim((string)($_POST['date'] ?? ''));

$allowedSessions = ['morning', 'afternoon', 'evening'];
if ($student_id === '' || $date === '' || !in_array($session, $allowedSessions, true)) {
    http_response_code(400);
    exit('invalid input');
}

$teacher_id = (string)$_SESSION['id'];
$markedBy   = (string)$_SESSION['name'];

$conn = db_mysqli();

$scope = $conn->prepare(
    'SELECT 1 FROM student_info si
     INNER JOIN teacher_subject_allocation tsa
       ON tsa.standard = si.standard AND tsa.section = si.section AND tsa.teacher_id = ?
     WHERE si.id = ? LIMIT 1'
);
$scope->bind_param('ss', $teacher_id, $student_id);
$scope->execute();
if ($scope->get_result()->num_rows === 0) {
    http_response_code(403);
    exit('forbidden');
}
$scope->close();

$check = $conn->prepare('SELECT id FROM attendance WHERE id = ? AND date = ? LIMIT 1');
$check->bind_param('ss', $student_id, $date);
$check->execute();
$exists = $check->get_result()->num_rows > 0;
$check->close();

if (!$exists) {
    $nameStmt = $conn->prepare('SELECT name FROM student_info WHERE id = ? LIMIT 1');
    $nameStmt->bind_param('s', $student_id);
    $nameStmt->execute();
    $nameRow = $nameStmt->get_result()->fetch_assoc();
    $nameStmt->close();
    $studentName = (string)($nameRow['name'] ?? '');

    $sql = "INSERT INTO attendance (id, name, date, `$session`, teacher_id, markedBy) VALUES (?, ?, ?, ?, ?, ?)";
    $ins = $conn->prepare($sql);
    $ins->bind_param('ssssss', $student_id, $studentName, $date, $status, $teacher_id, $markedBy);
    $ins->execute();
    $ins->close();
} else {
    $sql = "UPDATE attendance SET `$session` = ?, teacher_id = ?, markedBy = ? WHERE id = ? AND date = ?";
    $upd = $conn->prepare($sql);
    $upd->bind_param('sssss', $status, $teacher_id, $markedBy, $student_id, $date);
    $upd->execute();
    $upd->close();
}

echo 'ok';
