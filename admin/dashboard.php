<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Admin Dashboard';

$students = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) c FROM student_info"))['c'] ?? 0;
$teachers = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) c FROM user_login WHERE access=1"))['c'] ?? 0;
$homeworks = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) c FROM homeworks"))['c'] ?? 0;
$attendanceToday = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) c FROM attendance WHERE date=CURDATE()"))['c'] ?? 0;
$notifications = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) c FROM notification WHERE date=CURDATE()"))['c'] ?? 0;

$recentHw = mysqli_query($db, "SELECT subject_name, standard, section, date, title FROM homeworks ORDER BY id DESC LIMIT 5");
$recentNotifs = mysqli_query($db, "SELECT id, notification, sentBy, date, time FROM notification ORDER BY date DESC, time DESC LIMIT 5");

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main id="main" class="main">
  <div class="pagetitle">
    <h1>Admin Dashboard</h1>
    <nav><ol class="breadcrumb"><li class="breadcrumb-item active">Overview</li></ol></nav>
  </div>

  <section class="section dashboard">
    <div class="row">
      <div class="col-lg-3 col-md-6"><div class="card info-card"><div class="card-body"><h5 class="card-title">Students</h5><h6><?= e((string)$students) ?></h6></div></div></div>
      <div class="col-lg-3 col-md-6"><div class="card info-card"><div class="card-body"><h5 class="card-title">Teachers</h5><h6><?= e((string)$teachers) ?></h6></div></div></div>
      <div class="col-lg-3 col-md-6"><div class="card info-card"><div class="card-body"><h5 class="card-title">Homework</h5><h6><?= e((string)$homeworks) ?></h6></div></div></div>
      <div class="col-lg-3 col-md-6"><div class="card info-card"><div class="card-body"><h5 class="card-title">Attendance Today</h5><h6><?= e((string)$attendanceToday) ?></h6></div></div></div>
    </div>

    <div class="row">
      <div class="col-lg-8">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Recent Homework</h5>
            <div class="table-responsive">
              <table class="table table-sm">
                <thead><tr><th>Title</th><th>Subject</th><th>Class</th><th>Date</th></tr></thead>
                <tbody>
                <?php while ($r = mysqli_fetch_assoc($recentHw)): ?>
                  <tr>
                    <td><?= e($r['title']) ?></td>
                    <td><?= e($r['subject_name']) ?></td>
                    <td><?= e((string)$r['standard'] . '-' . $r['section']) ?></td>
                    <td><?= e($r['date']) ?></td>
                  </tr>
                <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Recent Notifications</h5>
            <ul class="list-group list-group-flush">
              <?php while ($n = mysqli_fetch_assoc($recentNotifs)): ?>
                <li class="list-group-item">
                  <div class="small text-muted"><?= e($n['date'] . ' ' . $n['time']) ?></div>
                  <strong><?= e($n['id']) ?></strong>
                  <div><?= e($n['notification']) ?></div>
                </li>
              <?php endwhile; ?>
            </ul>
          </div>
        </div>
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Quick Actions</h5>
            <div class="d-grid gap-2">
              <a class="btn btn-primary btn-sm" href="students.php">Manage Students</a>
              <a class="btn btn-success btn-sm" href="teachers.php">Manage Teachers</a>
              <a class="btn btn-warning btn-sm" href="attendance.php">Update Attendance</a>
              <a class="btn btn-info btn-sm" href="homework.php">Manage Homework</a>
              <a class="btn btn-secondary btn-sm" href="marks.php">Manage Marks</a>
            </div>
            <p class="mt-3 mb-0 small text-muted">Notifications created today: <?= e((string)$notifications) ?></p>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
