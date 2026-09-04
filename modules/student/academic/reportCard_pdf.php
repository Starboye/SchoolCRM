<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/portal.php';
require_login(0);

require __DIR__ . '/../../../dompdf-3.1.4/dompdf/autoload.inc.php';

use Dompdf\Dompdf;

$id = (string)$_SESSION['id'];
$studentName = (string)$_SESSION['name'];
$selectedTerm = $_GET['term'] ?? 'Term 1';

$conn = db_mysqli();

$stmt = $conn->prepare('SELECT * FROM student_info WHERE id = ? LIMIT 1');
$stmt->bind_param('s', $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    die('Student record not found.');
}

$standard = (string)($student['standard'] ?? '');
$section = (string)($student['section'] ?? '');

$subjects = ['english', 'tamil', 'maths', 'science', 'social'];
$marks = array_fill_keys($subjects, 0);
$total = 0;

$marksStmt = $conn->prepare('SELECT * FROM marks_new WHERE id = ? AND testName = ? LIMIT 1');
$marksStmt->bind_param('ss', $id, $selectedTerm);
$marksStmt->execute();
$marksRow = $marksStmt->get_result()->fetch_assoc();
$marksStmt->close();

foreach ($subjects as $s) {
    $marks[$s] = (int)($marksRow[$s] ?? 0);
    $total += $marks[$s];
}
$maxMarks = ((int)($marksRow['totalMarks'] ?? 100)) * count($subjects);
$schoolName = e(APP_NAME);

$html = "
<style>
body { font-family: DejaVu Sans, sans-serif; }
table { width:100%; border-collapse: collapse; }
td, th { border:1px solid #333; padding:8px; }
h1,h2,h3 { text-align:center; }
</style>

<h1>{$schoolName}</h1>
<h2>Report Card - " . e($selectedTerm) . "</h2>

<p><strong>Name:</strong> " . e($studentName) . "<br>
<strong>Class:</strong> " . e($standard) . " - " . e($section) . "<br>
<strong>Student ID:</strong> " . e($id) . "</p>

<h3>Marks</h3>

<table>
<tr><th>Subject</th><th>Marks</th><th>Out of</th></tr>";

foreach ($subjects as $s) {
    $label = ucfirst($s);
    $m = $marks[$s];
    $outOf = (int)($marksRow['totalMarks'] ?? 100);
    $html .= "<tr><td>{$label}</td><td>{$m}</td><td>{$outOf}</td></tr>";
}

$html .= "
<tr><td><strong>Total</strong></td><td><strong>{$total}</strong></td><td><strong>{$maxMarks}</strong></td></tr>
</table>

<p style='margin-top:50px;'>_____________________________<br>Class Teacher Signature</p>
";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('ReportCard_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $selectedTerm) . '.pdf', ['Attachment' => true]);
exit;
