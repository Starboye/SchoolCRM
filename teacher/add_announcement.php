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

    if ($_GET['action'] === 'sections') {
        $standard = $_GET['standard'];
        $res = $conn->query("SELECT section FROM class_sections WHERE standard='$standard'");
        echo "<option value=''>Select</option>";
        while ($r = $res->fetch_assoc()) {
            echo "<option value='{$r['section']}'>{$r['section']}</option>";
        }
        exit;
    }

    if ($_GET['action'] === 'students') {
        $standard = $_GET['standard'];
        $section  = $_GET['section'];

        $res = $conn->query("
            SELECT id, name 
            FROM student_info 
            WHERE standard='$standard' AND section='$section'
            ORDER BY name
        ");

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

    $message     = trim($_POST['message']);
    $target_type = $_POST['target_type'];
    $standard    = $_POST['standard'] ?? null;
    $section     = $_POST['section'] ?? null;
    $student_id  = $_POST['student_id'] ?? null;

    // if ($target_type === 'all') {
    //     $receiver_id = 'ALL';
    // }
    // elseif ($target_type === 'class') {
    //     $receiver_id = "CLASS_{$standard}_{$section}";
    // }
    // elseif ($target_type === 'student') {
    //     $receiver_id = $student_id;
    // }
    // else {
    //     $receiver_id = $student_id;
    // }

    if ($target_type === 'all') {
    $receiver_id = 'ALL';
    }
    elseif ($target_type === 'class') {
        $receiver_id = strtoupper("CLASS_{$standard}_{$section}");
    }
    elseif ($target_type === 'student') {
        $receiver_id = $student_id;
    }
    else {
        $receiver_id = $student_id;
    }


    $date = date("Y-m-d");
    $time = date("H:i");
    $status = 0;

    $stmt = $conn->prepare("
        INSERT INTO notification
        (id, notification, sentBy, date, time, status)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sssssi",
        $receiver_id,
        $message,
        $teacher_id,
        $date,
        $time,
        $status
    );

    if ($stmt->execute()) {
        $msg = "Announcement sent successfully";
    } else {
        $msg = "Failed to send announcement";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Announcement</title>

    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">

    <script>
        function loadSections() {
            const standard = document.getElementById("standard").value;
            fetch(`add_announcement.php?action=sections&standard=${standard}`)
                .then(res => res.text())
                .then(data => document.getElementById("section").innerHTML = data);
        }

        function loadStudents() {
            const standard = document.getElementById("standard").value;
            const section  = document.getElementById("section").value;

            fetch(`add_announcement.php?action=students&standard=${standard}&section=${section}`)
                .then(res => res.text())
                .then(data => document.getElementById("student_id").innerHTML = data);
        }

        function toggleTarget(sel) {
            document.getElementById("classBox").style.display =
                (sel.value !== 'all') ? 'block' : 'none';

            document.getElementById("studentBox").style.display =
                (sel.value === 'student') ? 'block' : 'none';
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

    <h4>Add Announcement</h4>

    <?php if ($msg): ?>
        <div class="alert alert-info"><?= $msg ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="mb-3">
            <label>Send To</label>
                <select name="target_type" class="form-control" onchange="toggleTarget(this)" required>
                    <option value="all">All Students</option>
                    <option value="class">Entire Class</option>
                    <option value="student">Single Student</option>
                </select>

        </div>

        

        <div id="classBox" style="display:none;">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Class</label>
                    <select id="standard" name="standard" class="form-control"
                            onchange="loadSections()" >
                        <option value="">Select</option>
                        <?php
                        $res = $conn->query("SELECT DISTINCT standard FROM class_sections ORDER BY standard");
                        while ($r = $res->fetch_assoc()) {
                            echo "<option value='{$r['standard']}'>{$r['standard']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Section</label>
                    <select id="section" name="section" class="form-control"
                            onchange="loadStudents()">
                        <option value="">Select</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="mb-3" id="studentBox" style="display:none;">
            <label>Select Student</label>
            <select id="student_id" name="student_id" class="form-control"></select>
        </div>

        <div class="mb-3">
            <label>Announcement</label>
            <textarea name="message" class="form-control" rows="4" required></textarea>
        </div>

        <button class="btn btn-primary">Send Announcement</button>

    </form>

</div>
</main>
</body>
</html>
