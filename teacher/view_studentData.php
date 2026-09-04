<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/teacher_auth.php';

$db = db_mysqli();
$teacher_id = $teacherId;

function teacher_can_view_student(mysqli $db, string $teacherId, string $studentId): bool {
    $stmt = $db->prepare(
        'SELECT 1 FROM student_info si
         INNER JOIN teacher_subject_allocation tsa
           ON tsa.standard = si.standard AND tsa.section = si.section AND tsa.teacher_id = ?
         WHERE si.id = ? LIMIT 1'
    );
    $stmt->bind_param('ss', $teacherId, $studentId);
    $stmt->execute();
    $ok = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $ok;
}

if (isset($_POST['ajax'])) {
    if (!isset($_SESSION['access']) || (int)$_SESSION['access'] !== 1) {
        http_response_code(403);
        exit('Unauthorized');
    }

    $student_id = trim((string)($_POST['student_id'] ?? ''));
    $tab = (string)($_POST['tab'] ?? '');

    if ($student_id === '' || !teacher_can_view_student($db, $teacher_id, $student_id)) {
        http_response_code(403);
        exit('Forbidden');
    }

    if ($tab === 'personal') {
        $stmt = $db->prepare('SELECT * FROM student_info WHERE id = ? LIMIT 1');
        $stmt->bind_param('s', $student_id);
        $stmt->execute();
        $s = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$s) {
            exit('<div class="alert alert-warning">Student not found.</div>');
        }
        ?>
        <div class="card">
            <div class="card-body">
                <h4><?= e((string)$s['name']) ?> (<?= e((string)$s['standard']) ?><?= e((string)$s['section']) ?>)</h4>
                <hr>
                <p><b>DOB:</b> <?= e((string)($s['dateOfBirth'] ?? '')) ?></p>
                <p><b>Gender:</b> <?= e((string)($s['gender'] ?? '')) ?></p>
                <p><b>Blood Group:</b> <?= e((string)($s['bloodGroup'] ?? '')) ?></p>
                <p><b>Phone:</b> <?= e((string)($s['phone'] ?? '')) ?></p>
                <p><b>Email:</b> <?= e((string)($s['emailID'] ?? '')) ?></p>
                <p><b>Address:</b> <?= e((string)($s['address'] ?? '')) ?></p>
            </div>
        </div>
        <?php
        exit;
    }

    if ($tab === 'academic') {
        $hasExamSchema = db_table_exists($db, 'marks_master') && db_table_exists($db, 'exams');
        ?>
        <table class="table table-bordered">
            <thead>
            <tr><th>Exam</th><th>Subject</th><th>Marks</th></tr>
            </thead>
            <tbody>
            <?php
            if ($hasExamSchema) {
                $stmt = $db->prepare(
                    'SELECT e.exam_name, sub.subject_name, md.marks_obtained, md.total_marks
                     FROM marks_master mm
                     JOIN exams e ON e.exam_id = mm.exam_id
                     JOIN marks_details md ON md.mark_id = mm.mark_id
                     JOIN subjects sub ON sub.id = md.subject_id
                     WHERE mm.student_id = ?'
                );
                $stmt->bind_param('s', $student_id);
                $stmt->execute();
                $q = $stmt->get_result();
                $rows = false;
                while ($r = $q->fetch_assoc()) {
                    $rows = true;
                    echo '<tr><td>' . e((string)$r['exam_name']) . '</td><td>' . e((string)$r['subject_name']) . '</td><td>' . e((string)$r['marks_obtained']) . '/' . e((string)$r['total_marks']) . '</td></tr>';
                }
                $stmt->close();
                if (!$rows) {
                    echo '<tr><td colspan="3" class="text-muted">No exam marks on file.</td></tr>';
                }
            } else {
                $stmt = $db->prepare('SELECT testName, subjectName, marksObtained, totalMarks FROM marks WHERE id = ? ORDER BY testName, subjectName');
                $stmt->bind_param('s', $student_id);
                $stmt->execute();
                $q = $stmt->get_result();
                $rows = false;
                while ($r = $q->fetch_assoc()) {
                    $rows = true;
                    echo '<tr><td>' . e((string)$r['testName']) . '</td><td>' . e((string)$r['subjectName']) . '</td><td>' . e((string)$r['marksObtained']) . '/' . e((string)$r['totalMarks']) . '</td></tr>';
                }
                $stmt->close();
                if (!$rows) {
                    echo '<tr><td colspan="3" class="text-muted">No marks on file.</td></tr>';
                }
            }
            ?>
            </tbody>
        </table>
        <?php
        exit;
    }

    if ($tab === 'attendance') {
        $stmt = $db->prepare('SELECT date, morning, afternoon, evening FROM attendance WHERE id = ? ORDER BY date DESC LIMIT 30');
        $stmt->bind_param('s', $student_id);
        $stmt->execute();
        $q = $stmt->get_result();
        ?>
        <table class="table table-striped">
            <thead><tr><th>Date</th><th>Morning</th><th>Afternoon</th><th>Evening</th></tr></thead>
            <tbody>
            <?php while ($r = $q->fetch_assoc()): ?>
                <tr>
                    <td><?= e((string)$r['date']) ?></td>
                    <td><?= $r['morning'] ? 'Present' : 'Absent' ?></td>
                    <td><?= $r['afternoon'] ? 'Present' : 'Absent' ?></td>
                    <td><?= $r['evening'] ? 'Present' : 'Absent' ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php
        $stmt->close();
        exit;
    }

    if ($tab === 'homework') {
        $clsStmt = $db->prepare('SELECT standard, section FROM student_info WHERE id = ? LIMIT 1');
        $clsStmt->bind_param('s', $student_id);
        $clsStmt->execute();
        $cls = $clsStmt->get_result()->fetch_assoc();
        $clsStmt->close();
        if ($cls) {
            $hwStmt = $db->prepare('SELECT subject_name, title, description, date FROM homeworks WHERE standard = ? AND section = ? ORDER BY date DESC');
            $hwStmt->bind_param('ss', $cls['standard'], $cls['section']);
            $hwStmt->execute();
            $q = $hwStmt->get_result();
            $found = false;
            while ($r = $q->fetch_assoc()) {
                $found = true;
                ?>
                <div class="card mb-2">
                    <div class="card-body">
                        <b><?= e((string)$r['subject_name']) ?></b> (<?= e((string)$r['date']) ?>)
                        <p><b><?= e((string)$r['title']) ?></b></p>
                        <p><?= e((string)$r['description']) ?></p>
                    </div>
                </div>
                <?php
            }
            $hwStmt->close();
            if (!$found) {
                echo '<div class="alert alert-info">No homework assigned.</div>';
            }
        }
        exit;
    }

    http_response_code(400);
    exit('Invalid tab');
}

$pageTitle = 'Student Lookup';
include __DIR__ . '/includes/teacher_header.php';
?>
<aside id="sidebar" class="sidebar">
  <div id="sidebar-container"></div>
  <script src="<?= e(app_url('teacher/includes/loadteacherSidebar.js')) ?>"></script>
</aside>

<main id="main" class="main">
<div class="container mt-4">

<h3>Student Profile</h3>
<hr>

<label>Select Student</label>
<select id="student_id" class="form-control" style="max-width:350px;">
    <option value="">-- Select Student --</option>
    <?php
    $listStmt = $db->prepare(
        'SELECT DISTINCT si.id, si.name, si.standard, si.section
         FROM student_info si
         INNER JOIN teacher_subject_allocation tsa
           ON tsa.standard = si.standard AND tsa.section = si.section AND tsa.teacher_id = ?
         ORDER BY si.name'
    );
    $listStmt->bind_param('s', $teacher_id);
    $listStmt->execute();
    $q = $listStmt->get_result();
    while ($s = $q->fetch_assoc()) {
        echo '<option value="' . e((string)$s['id']) . '">' . e((string)$s['name']) . ' (' . e((string)$s['standard']) . e((string)$s['section']) . ')</option>';
    }
    $listStmt->close();
    ?>
</select>

<ul class="nav nav-tabs mt-3" id="tabs" style="display:none;">
    <li class="nav-item"><a class="nav-link active" data-tab="personal">Personal</a></li>
    <li class="nav-item"><a class="nav-link" data-tab="academic">Academics</a></li>
    <li class="nav-item"><a class="nav-link" data-tab="attendance">Attendance</a></li>
    <li class="nav-item"><a class="nav-link" data-tab="homework">Homework</a></li>
</ul>

<div id="content" class="mt-3"></div>

</div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$("#student_id").change(function(){
    if(!this.value) return;
    $("#tabs").show();
    loadTab("personal");
});

$(".nav-link").click(function(){
    $(".nav-link").removeClass("active");
    $(this).addClass("active");
    loadTab($(this).data("tab"));
});

function loadTab(tab){
    $.post("view_studentData.php",{
        ajax:1,
        tab:tab,
        student_id:$("#student_id").val()
    },function(res){
        $("#content").html(res);
    }).fail(function(xhr){
        if (xhr.status === 403) {
            alert("Session expired or access denied. Please sign in again.");
            window.location.href = "<?= e(app_url('index.php')) ?>";
        }
    });
}
</script>

<?php include __DIR__ . '/includes/teacher_footer.php'; ?>
