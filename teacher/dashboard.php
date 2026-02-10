<?php
session_start();
include "includes/teacher_header.php";
?>

<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
  <div id="sidebar-container"></div>
  <script src="../teacher/includes/loadteacherSidebar.js"></script>
</aside>
<!-- ======= Sidebar ======= -->

<main id="main" class="main">

<?php
// DB Connection
$conn = new mysqli("localhost", "root", "", "asimos");
if ($conn->connect_error) {
    die("DB Connection failed");
}

$teacherId = $_SESSION['id'];
$today = date("Y-m-d");

/* =========================
   TOP METRICS
   ========================= */

// Homeworks assigned
$hwCount = $conn->query("
    SELECT COUNT(*) AS c 
    FROM homeworks 
    WHERE teacher_id = '$teacherId'
")->fetch_assoc()['c'];

// Attendance marked today
$attCount = $conn->query("
    SELECT COUNT(*) AS c 
    FROM attendance 
    WHERE markedBy = '$teacherId' 
      AND date = '$today'
")->fetch_assoc()['c'];

// Announcements sent
$annCount = $conn->query("
    SELECT COUNT(*) AS c 
    FROM notification 
    WHERE id = '$teacherId' 
      AND status = 0
")->fetch_assoc()['c'];

/* =========================
   TODAY'S CLASSES (SAFE)
   ========================= */
$scheduleQ = $conn->query("
    SELECT DISTINCT standard, section
    FROM teacher_subject_allocation
    WHERE teacher_id = '$teacherId'
");

/* =========================
   PENDING TASKS
   ========================= */

// Pending attendance
$pendingAttendance = $conn->query("
    SELECT COUNT(*) AS c
    FROM teacher_subject_allocation tsa
    WHERE tsa.teacher_id = '$teacherId'
      AND NOT EXISTS (
        SELECT 1 FROM attendance a
        WHERE a.date = '$today'
          AND a.markedBy = '$teacherId'
      )
")->fetch_assoc()['c'];

// Pending marks
$pendingMarks = $conn->query("
    SELECT COUNT(*) AS c
    FROM exams e
    WHERE e.created_by = '$teacherId'
      AND NOT EXISTS (
        SELECT 1 FROM marks_master mm
        WHERE mm.exam_id = e.exam_id
      )
")->fetch_assoc()['c'];
?>

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
          <?php if($scheduleQ->num_rows > 0){
            while($r = $scheduleQ->fetch_assoc()){ ?>
              <li>• Class <?= $r['standard'].$r['section']; ?></li>
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
