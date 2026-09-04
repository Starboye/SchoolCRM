<?php
require_once __DIR__ . '/includes/teacher_auth.php';

$conn = db_mysqli();
$teacher_id = $teacherId;
$date = isset($_GET['date']) ? (string)$_GET['date'] : date('Y-m-d');
$session = isset($_GET['session']) ? (string)$_GET['session'] : '';

$pageTitle = 'Attendance';
include __DIR__ . '/includes/teacher_header.php';
?>
<aside id="sidebar" class="sidebar">
  <div id="sidebar-container"></div>
  <script src="<?= e(app_url('teacher/includes/loadteacherSidebar.js')) ?>"></script>
</aside>

<main id="main" class="main">
<div class="container mt-4">

<h3>Realtime Attendance</h3>
<hr>

<form method="GET">
    <label>Date</label>
    <input type="date" name="date" class="form-control" value="<?= e($date) ?>" required>

    <label class="mt-3">Session</label>
    <select name="session" class="form-control" required>
        <option value="">-- choose --</option>
        <option value="morning" <?= $session === 'morning' ? 'selected' : '' ?>>Morning</option>
        <option value="afternoon" <?= $session === 'afternoon' ? 'selected' : '' ?>>Afternoon</option>
        <option value="evening" <?= $session === 'evening' ? 'selected' : '' ?>>Evening</option>
    </select>

    <button class="btn btn-primary mt-3">Load Students</button>
</form>

<?php
if ($session !== '') {
    $studentQuery = "
        SELECT DISTINCT si.id, si.name
        FROM student_info si
        INNER JOIN teacher_subject_allocation tsa
          ON tsa.standard = si.standard
         AND tsa.section = si.section
         AND tsa.teacher_id = ?
        ORDER BY si.name
    ";
    $stmt = $conn->prepare($studentQuery);
    $stmt->bind_param('s', $teacher_id);
    $stmt->execute();
    $studentResult = $stmt->get_result();
    echo '<hr>';
    if ($studentResult->num_rows === 0) {
        echo '<div class="alert alert-info">No students found for your allocated classes.</div>';
    } else {
?>
<table class="table table-bordered mt-3">
<thead>
<tr>
    <th>Student</th>
    <th style="width:150px;">Control</th>
    <th>Status</th>
</tr>
</thead>
<tbody>
<?php while ($s = $studentResult->fetch_assoc()): ?>
<tr id="row<?= e((string)$s['id']) ?>">
<td><?= e((string)$s['name']) ?></td>
<td>
<button class="btn btn-success btn-sm" onclick="markAttendance('<?= e((string)$s['id']) ?>',1)">P</button>
<button class="btn btn-danger btn-sm" onclick="markAttendance('<?= e((string)$s['id']) ?>',0)">A</button>
</td>
<td id="status<?= e((string)$s['id']) ?>"></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
<?php
    }
    $stmt->close();
}
?>
</div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function markAttendance(student_id, status) {
    let session = document.querySelector("select[name='session']").value;
    let date    = document.querySelector("input[name='date']").value;
    if (session === '') {
        alert("Select session first");
        return;
    }
    $.ajax({
        url: "<?= e(app_url('forms/updateAttendanceAjax.php')) ?>",
        type: "POST",
        data: { student_id: student_id, status: status, session: session, date: date },
        success: function() {
            if (status == 1) {
                document.getElementById("status" + student_id).innerHTML =
                    "<span style='color:green;font-weight:bold;'>Present</span>";
            } else {
                document.getElementById("status" + student_id).innerHTML =
                    "<span style='color:red;font-weight:bold;'>Absent</span>";
            }
        },
        error: function() {
            alert("Failed to save attendance. Please sign in again.");
        }
    });
}
</script>

<?php include __DIR__ . '/includes/teacher_footer.php'; ?>
