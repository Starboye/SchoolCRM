<?php
session_start();

/* ---------------- AUTH ---------------- */
if (!isset($_SESSION['id'])) {
    header("Location: /Asimos/index.php");
    exit;
}

$teacher_id = $_SESSION['id'];

/* ---------------- DB ---------------- */
$conn = new mysqli("localhost", "root", "", "asimos");
if ($conn->connect_error) {
    die("DB connection failed");
}

/* ---------------- AJAX ---------------- */
if (isset($_GET['action'])) {

    // Load sections
    if ($_GET['action'] === 'sections') {
        $std = $_GET['standard'];
        $res = $conn->query("SELECT section FROM class_sections WHERE standard='$std'");
        echo "<option value=''>Select</option>";
        while ($r = $res->fetch_assoc()) {
            echo "<option value='{$r['section']}'>{$r['section']}</option>";
        }
        exit;
    }

    // Load students
    if ($_GET['action'] === 'students') {
        $std = $_GET['standard'];
        $sec = $_GET['section'];
        $res = $conn->query("SELECT id, name FROM student_info WHERE standard='$std' AND section='$sec'");
        echo "<option value=''>Select</option>";
        while ($r = $res->fetch_assoc()) {
            echo "<option value='{$r['id']}'>{$r['name']} ({$r['id']})</option>";
        }
        exit;
    }

    // Load subjects
    if ($_GET['action'] === 'subjects') {
        $res = $conn->query("SELECT subject_name FROM subjects ORDER BY subject_name");
        while ($r = $res->fetch_assoc()) {
            echo "
            <div class='col-md-4'>
                <label>{$r['subject_name']}</label>
                <input type='number' name='marks[{$r['subject_name']}][obtained]' class='form-control' placeholder='Obtained'>
                <input type='number' name='marks[{$r['subject_name']}][total]' class='form-control mt-1' placeholder='Total'>
            </div>";
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Marks</title>
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">

<script>
function loadSections() {
    fetch(`add_marks.php?action=sections&standard=${standard.value}`)
        .then(r => r.text()).then(d => section.innerHTML = d);
}

function loadStudents() {
    fetch(`add_marks.php?action=students&standard=${standard.value}&section=${section.value}`)
        .then(r => r.text()).then(d => student.innerHTML = d);

    fetch(`add_marks.php?action=subjects`)
        .then(r => r.text()).then(d => document.getElementById("marksBox").innerHTML = d);
}

function toggleStudent(v) {
    document.getElementById("studentBox").style.display =
        (v === 'student') ? 'block' : 'none';
}
</script>
</head>

<?php include "includes/teacher_header.php"; ?>
<aside id="sidebar" class="sidebar">
  <div id="sidebar-container"></div>
  <script src="../teacher/includes/loadteacherSidebar.js"></script>
</aside>

<body>
<main id="main" class="main">
<div class="container mt-4">

<h4>Add Marks</h4>

<form method="POST">

<div class="row mb-3">
    <div class="col-md-3">
        <label>Class</label>
        <select id="standard" name="standard" class="form-control" onchange="loadSections()" required>
            <option value="">Select</option>
            <?php
            $r = $conn->query("SELECT DISTINCT standard FROM class_sections ORDER BY standard");
            while ($row = $r->fetch_assoc()) {
                echo "<option>{$row['standard']}</option>";
            }
            ?>
        </select>
    </div>

    <div class="col-md-3">
        <label>Section</label>
        <select id="section" name="section" class="form-control" onchange="loadStudents()" required></select>
    </div>

    <div class="col-md-6">
        <label>Exam / Assessment Name</label>
        <input type="text" name="exam_name" class="form-control" placeholder="Term 1 / Unit Test / Surprise Test" required>
    </div>
</div>

<div class="mb-3">
    <label>Apply To</label>
    <select name="target" class="form-control" onchange="toggleStudent(this.value)">
        <option value="class">Entire Class</option>
        <option value="student">Single Student</option>
    </select>
</div>

<div class="mb-3" id="studentBox" style="display:none;">
    <label>Select Student</label>
    <select id="student" name="student_id" class="form-control"></select>
</div>

<hr>

<h5>Enter Marks</h5>
<div class="row" id="marksBox"></div>

<hr>
<button class="btn btn-primary">Save Marks</button>

</form>

</div>
</main>
</body>
</html>
