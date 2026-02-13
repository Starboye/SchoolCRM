<?php
session_start();

if (!isset($_SESSION["access"], $_SESSION["name"], $_SESSION["id"]) || $_SESSION["access"] != 0) {
  die("Unauthorized");
}

require __DIR__ . "/dompdf-3.1.4/dompdf/autoload.inc.php";   // If installed via Composer, use vendor/autoload.php instead.

use Dompdf\Dompdf;

$id = $_SESSION['id'];
$studentName = $_SESSION['name'];
$selectedTerm = $_GET['term'] ?? "Term 1";

// ------------- DB Connection ----------------
$conn = new mysqli("localhost", "root", "", "asimos");
if ($conn->connect_error) die("DB Error");

// ---------- Fetch student info ----------
$student = $conn->query("SELECT * FROM student_info WHERE id='$id'")->fetch_assoc();
$standard = $student['standard'] ?? "";
$section  = $student['section'] ?? "";

// ---------- Fetch student marks ----------
$subjects = ["english","tamil","maths","science","social"];
$marks = array_fill_keys($subjects, 0);
$total = 0;

$marksRow = $conn->query("SELECT * FROM marks_new WHERE id='$id' AND testName='$selectedTerm' LIMIT 1")->fetch_assoc();
foreach ($subjects as $s) {
  $marks[$s] = $marksRow[$s] ?? 0;
  $total += $marks[$s];
}
$maxMarks = ($marksRow['totalMarks'] ?? 100) * count($subjects);

// ---------- Build HTML for PDF ----------
$html = "
<style>
body { font-family: DejaVu Sans, sans-serif; }
table { width:100%; border-collapse: collapse; }
td, th { border:1px solid #333; padding:8px; }
h1,h2,h3 { text-align:center; }
</style>

<h1>ASIMOS School</h1>
<h2>Report Card - $selectedTerm</h2>

<p><strong>Name:</strong> $studentName<br>
<strong>Class:</strong> $standard - $section<br>
<strong>Student ID:</strong> $id</p>

<h3>Marks</h3>

<table>
<tr><th>Subject</th><th>Marks</th><th>Out of</th></tr>";

foreach ($subjects as $s) {
  $label = ucfirst($s);
  $m = $marks[$s];
  $html .= "<tr><td>$label</td><td>$m</td><td>{$marksRow['totalMarks']}</td></tr>";
}

$html .= "
<tr><td><strong>Total</strong></td><td><strong>$total</strong></td><td><strong>$maxMarks</strong></td></tr>
</table>

<p style='margin-top:50px;'>_____________________________<br>Class Teacher Signature</p>
";

// ---------- Generate PDF ----------
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper("A4", "portrait");
$dompdf->render();
$dompdf->stream("ReportCard_$selectedTerm.pdf", ["Attachment" => true]);
exit;
?>
