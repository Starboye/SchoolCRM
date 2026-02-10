<?php
session_start();

/* ---------------- AUTH ---------------- */
if (!isset($_SESSION['id'])) {
    header("Location: /Asimos/index.php");
    exit;
}

$teacher_id = $_SESSION['id'];

/* ---------------- DB CONNECTION ---------------- */
$conn = new mysqli("localhost", "root", "", "asimos");
if ($conn->connect_error) {
    die("Database connection failed");
}

/* ---------------- AJAX HANDLERS ---------------- */
if (isset($_GET['action'])) {

    /* Load sections by class */
    if ($_GET['action'] === 'sections') {
        $standard = $_GET['standard'];
        $res = $conn->query("SELECT section FROM class_sections WHERE standard='$standard'");
        echo "<option value=''>Select</option>";
        while ($r = $res->fetch_assoc()) {
            echo "<option value='{$r['section']}'>{$r['section']}</option>";
        }
        exit;
    }

    /* Load subjects by class + section */
    if ($_GET['action'] === 'subjects') {
        $standard = $_GET['standard'];
        $section  = $_GET['section'];

        $sql = "
        SELECT s.subject_name
        FROM subjects s
        JOIN class_subjects cs ON cs.subject_id = s.id
        JOIN class_sections c ON c.id = cs.class_section_id
        WHERE c.standard='$standard' AND c.section='$section'
        ";

        $res = $conn->query($sql);
        echo "<option value=''>Select</option>";
        while ($r = $res->fetch_assoc()) {
            echo "<option value='{$r['subject_name']}'>{$r['subject_name']}</option>";
        }
        exit;
    }

    /* Load students by class + section */
    if ($_GET['action'] === 'students') {
        $standard = $_GET['standard'];
        $section  = $_GET['section'];

        $res = $conn->query(
            "SELECT id, name FROM student_info
             WHERE standard='$standard' AND section='$section'
             ORDER BY name"
        );

        echo "<option value=''>Select</option>";
        while ($r = $res->fetch_assoc()) {
            echo "<option value='{$r['id']}'>{$r['name']} ({$r['id']})</option>";
        }
        exit;
    }
}

/* ---------------- FORM SUBMIT ---------------- */
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $standard    = $_POST['standard'];
    $section     = $_POST['section'];
    $subject     = $_POST['subject_name'];
    $date        = $_POST['date'];
    $title       = $_POST['title'];
    $description = $_POST['description'];
    $target_type = $_POST['target_type'];
    $student_id  = ($target_type === 'student') ? $_POST['student_id'] : null;

    $sql = "INSERT INTO homeworks
            (subject_name, teacher_id, standard, section, date, title, description, target_type, student_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sissssssi",
        $subject,
        $teacher_id,
        $standard,
        $section,
        $date,
        $title,
        $description,
        $target_type,
        $student_id
    );

    if ($stmt->execute()) {
        $msg = "Homework added successfully";
    } else {
        $msg = "Failed to add homework";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Homework</title>

    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">

    <script>
        function loadSections() {
            const standard = document.getElementById("standard").value;
            fetch(`add_homework.php?action=sections&standard=${standard}`)
                .then(res => res.text())
                .then(data => document.getElementById("section").innerHTML = data);
        }

        function loadSubjects() {
            const standard = document.getElementById("standard").value;
            const section  = document.getElementById("section").value;

            fetch(`add_homework.php?action=subjects&standard=${standard}&section=${section}`)
                .then(res => res.text())
                .then(data => document.getElementById("subject").innerHTML = data);
        }

        function loadStudents() {
            const standard = document.getElementById("standard").value;
            const section  = document.getElementById("section").value;

            fetch(`add_homework.php?action=students&standard=${standard}&section=${section}`)
                .then(res => res.text())
                .then(data => document.getElementById("student_id").innerHTML = data);
        }

        function toggleStudent(sel) {
            document.getElementById("studentBox").style.display =
                (sel.value === 'student') ? 'block' : 'none';
        }
    </script>
</head>
<?php include "includes/teacher_header.php"; ?>
  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">
    <div id="sidebar-container"></div>
    <script src="../teacher/includes/loadteacherSidebar.js"></script>
  </aside>
  <!-- ======= Sidebar ======= -->
<body>


<main id="main" class="main">
<div class="container mt-4">

    <h4>Add Homework</h4>

    <?php if ($msg): ?>
        <div class="alert alert-info"><?= $msg ?></div>
    <?php endif; ?>

    <form method="POST">

        <!-- Class / Section / Subject -->
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Class</label>
                <select id="standard" name="standard" class="form-control" onchange="loadSections()" required>
                    <option value="">Select</option>
                    <?php
                    $res = $conn->query("SELECT DISTINCT standard FROM class_sections ORDER BY standard");
                    while ($r = $res->fetch_assoc()) {
                        echo "<option value='{$r['standard']}'>{$r['standard']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-3">
                <label>Section</label>
                <select id="section" name="section" class="form-control"
                        onchange="loadSubjects();loadStudents();" required>
                    <option value="">Select</option>
                </select>
            </div>

            <div class="col-md-6">
                <label>Subject</label>
                <select id="subject" name="subject_name" class="form-control" required>
                    <option value="">Select</option>
                </select>
            </div>
        </div>

        <!-- Target -->
        <div class="mb-3">
            <label>Assign To</label>
            <select name="target_type" class="form-control" onchange="toggleStudent(this)" required>
                <option value="class">Entire Class</option>
                <option value="student">Single Student</option>
            </select>
        </div>

        <div class="mb-3" id="studentBox" style="display:none;">
            <label>Select Student</label>
            <select id="student_id" name="student_id" class="form-control"></select>
        </div>

        <!-- Homework -->
        <div class="mb-3">
            <label>Date</label>
            <input type="date" name="date" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="4" required></textarea>
        </div>

        <button class="btn btn-primary">Add Homework</button>

    </form>

</div>
</main>

</body>
</html>
