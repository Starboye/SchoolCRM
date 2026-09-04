<?php
require_once __DIR__ . '/includes/teacher_auth.php';

$teacher_id = $teacherId;
$saveMessage = '';
$saveMessageType = '';

$conn = db_mysqli();

/* ---------------- POST: save marks ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $standard = trim((string)($_POST['standard'] ?? ''));
    $section  = trim((string)($_POST['section'] ?? ''));
    $testName = trim((string)($_POST['exam_name'] ?? ''));
    $target   = (string)($_POST['target'] ?? 'class');
    $marksData = $_POST['marks'] ?? [];
    $today    = date('Y-m-d');

    if ($standard === '' || $section === '' || $testName === '') {
        $saveMessage = 'Class, section, and exam name are required.';
        $saveMessageType = 'danger';
    } else {
        $studentIds = [];
        if ($target === 'student') {
            $sid = trim((string)($_POST['student_id'] ?? ''));
            if ($sid !== '') {
                $studentIds[] = $sid;
            }
        } else {
            $stmt = $conn->prepare('SELECT id FROM student_info WHERE standard = ? AND section = ?');
            $stmt->bind_param('ss', $standard, $section);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $studentIds[] = (string)$row['id'];
            }
            $stmt->close();
        }

        $saved = 0;
        foreach ($studentIds as $studentId) {
            foreach ($marksData as $subjectName => $vals) {
                $obtained = trim((string)($vals['obtained'] ?? ''));
                $total    = trim((string)($vals['total'] ?? ''));
                if ($obtained === '' && $total === '') {
                    continue;
                }

                $chk = $conn->prepare(
                    'SELECT 1 FROM marks WHERE id = ? AND subjectName = ? AND testName = ? LIMIT 1'
                );
                $chk->bind_param('sss', $studentId, $subjectName, $testName);
                $chk->execute();
                $exists = $chk->get_result()->num_rows > 0;
                $chk->close();

                if ($exists) {
                    $upd = $conn->prepare(
                        'UPDATE marks SET marksObtained = ?, totalMarks = ?, date = ? WHERE id = ? AND subjectName = ? AND testName = ?'
                    );
                    $upd->bind_param('ssssss', $obtained, $total, $today, $studentId, $subjectName, $testName);
                    if ($upd->execute()) {
                        $saved++;
                    }
                    $upd->close();
                } else {
                    $ins = $conn->prepare(
                        'INSERT INTO marks (id, subjectName, testName, date, marksObtained, totalMarks) VALUES (?, ?, ?, ?, ?, ?)'
                    );
                    $ins->bind_param('ssssss', $studentId, $subjectName, $testName, $today, $obtained, $total);
                    if ($ins->execute()) {
                        $saved++;
                    }
                    $ins->close();
                }
            }
        }

        if ($saved > 0) {
            $saveMessage = 'Marks saved successfully.';
            $saveMessageType = 'success';
        } else {
            $saveMessage = 'No marks were saved. Enter at least one subject mark.';
            $saveMessageType = 'warning';
        }
    }
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

$pageTitle = 'Add Marks';
include __DIR__ . '/includes/teacher_header.php';
?>
<aside id="sidebar" class="sidebar">
  <div id="sidebar-container"></div>
  <script src="<?= e(app_url('teacher/includes/loadteacherSidebar.js')) ?>"></script>
</aside>

<main id="main" class="main">
<div class="container mt-4">

<h4>Add Marks</h4>

<?php if ($saveMessage !== ''): ?>
<div class="alert alert-<?= e($saveMessageType) ?>"><?= e($saveMessage) ?></div>
<?php endif; ?>

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

<?php include __DIR__ . '/includes/teacher_footer.php'; ?>
