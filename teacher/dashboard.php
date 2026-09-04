<?php
$pageTitle = 'Dashboard';
include __DIR__ . '/includes/teacher_header.php';

$conn = db_mysqli();
$teacherId = (string)$_SESSION['id'];
$today = date('Y-m-d');

function dash_count(mysqli $conn, string $sql): int {
    $res = $conn->query($sql);
    if (!$res) {
        return 0;
    }
    $row = $res->fetch_assoc();
    return (int)($row['c'] ?? 0);
}

$tid = $conn->real_escape_string($teacherId);
$hwCount = dash_count($conn, "SELECT COUNT(*) AS c FROM homeworks WHERE teacher_id = '$tid'");
$attCount = dash_count($conn, "SELECT COUNT(*) AS c FROM attendance WHERE markedBy = '$tid' AND date = '$today'");
$annCount = dash_count($conn, "SELECT COUNT(*) AS c FROM notification WHERE id = '$tid' AND status = 0");

$scheduleQ = $conn->query("SELECT DISTINCT standard, section FROM teacher_subject_allocation WHERE teacher_id = '$tid'");
if (!$scheduleQ) {
    $scheduleQ = false;
}

$pendingAttendance = dash_count($conn,
    "SELECT COUNT(*) AS c FROM teacher_subject_allocation tsa
     WHERE tsa.teacher_id = '$tid'
       AND NOT EXISTS (SELECT 1 FROM attendance a WHERE a.date = '$today' AND a.markedBy = '$tid')"
);

$pendingMarks = 0;
if (db_table_exists($conn, 'exams') && db_table_exists($conn, 'marks_master')) {
    $pendingMarks = dash_count($conn,
        "SELECT COUNT(*) AS c FROM exams e
         WHERE e.created_by = '$tid'
           AND NOT EXISTS (SELECT 1 FROM marks_master mm WHERE mm.exam_id = e.exam_id)"
    );
}
?>

<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
  <div id="sidebar-container"></div>
  <script src="<?= e(app_url('teacher/includes/loadteacherSidebar.js')) ?>"></script>
</aside>

<main id="main" class="main">

<!-- PAGE TITLE -->
<div class="pagetitle">
  <h1>Teacher Dashboard</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
      <li class="breadcrumb-item active">Dashboard</li>
    </ol>
  </nav>
</div>

<section class="section dashboard">

  <!-- SUMMARY CARDS -->
  <div class="row">

    <div class="col-lg-4 col-md-6 d-flex">
      <div class="card info-card w-100 h-100">
        <div class="card-body">
          <h5 class="card-title">Homeworks Assigned</h5>
          <div class="d-flex align-items-center">
            <i class="bi bi-journal-text info-icon"></i>
            <div class="ps-3">
              <h6><?= $hwCount; ?></h6>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4 col-md-6 d-flex">
      <div class="card info-card w-100 h-100">
        <div class="card-body">
          <h5 class="card-title">Attendance Marked Today</h5>
          <div class="d-flex align-items-center">
            <i class="bi bi-clipboard-check info-icon"></i>
            <div class="ps-3">
              <h6><?= $attCount; ?></h6>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4 col-md-6 d-flex">
      <div class="card info-card w-100 h-100">
        <div class="card-body">
          <h5 class="card-title">Announcements Sent</h5>
          <div class="d-flex align-items-center">
            <i class="bi bi-megaphone info-icon"></i>
            <div class="ps-3">
              <h6><?= $annCount; ?></h6>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- NEW DASHBOARD ROW -->
<div class="row mt-4">

  <!-- TODAY'S CLASSES -->
  <div class="col-lg-4 col-md-6 d-flex">
    <div class="card w-100 h-100">
      <div class="card-body">
        <h5 class="card-title">📅 Today’s Classes</h5>
        <ul class="list-unstyled mb-0">
          <?php if ($scheduleQ && $scheduleQ->num_rows > 0) {
            while ($r = $scheduleQ->fetch_assoc()) { ?>
              <li>• Class <?= e((string)$r['standard']) ?><?= e((string)$r['section']) ?></li>
          <?php }} else { ?>
              <li>No classes assigned</li>
          <?php } ?>
        </ul>
      </div>
    </div>
  </div>

  <!-- PENDING TASKS -->
  <div class="col-lg-4 col-md-6 d-flex">
    <div class="card w-100 h-100">
      <div class="card-body">
        <h5 class="card-title">⚠️ Pending Tasks</h5>
        <ul class="list-unstyled mb-0">
          <li>• Attendance pending – <?= $pendingAttendance; ?> classes</li>
          <li>• Marks pending – <?= $pendingMarks; ?> exams</li>
        </ul>
      </div>
    </div>
  </div>

  <!-- QUICK ACTIONS -->
  <div class="col-lg-4 col-md-6 d-flex">
    <div class="card w-100 h-100">
      <div class="card-body">
        <h5 class="card-title">🚀 Quick Actions</h5>
        <div class="d-flex flex-wrap gap-2">
          <a href="attendance.php" class="btn btn-primary btn-sm">Mark Attendance</a>
          <a href="add_homework.php" class="btn btn-success btn-sm">Add Homework</a>
          <a href="add_marks.php" class="btn btn-warning btn-sm">Enter Marks</a>
        </div>
      </div>
    </div>
  </div>

</div>

</section>
</main>

<?php include __DIR__ . '/includes/teacher_footer.php'; ?>
