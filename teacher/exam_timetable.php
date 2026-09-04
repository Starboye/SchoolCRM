<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/teacher_auth.php';

$pageTitle = 'Exam Timetable';
$teacherId = (string)$_SESSION['id'];
$conn = db_mysqli();

$days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];

$slots = [];
$hasPlanner = db_table_exists($conn, 'timetable_slots');

if ($hasPlanner) {
    $sql = 'SELECT day_of_week, period_no, subject_name, standard, section
            FROM timetable_slots
            WHERE teacher_id = ?
            ORDER BY day_of_week, period_no';
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('s', $teacherId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $dow = (int)$row['day_of_week'];
            $row['day_label'] = $days[$dow] ?? ('Day ' . $dow);
            $slots[] = $row;
        }
        $stmt->close();
    }
}

include __DIR__ . '/includes/teacher_header.php';
?>
<aside id="sidebar" class="sidebar">
  <div id="sidebar-container"></div>
  <script src="<?= e(app_url('teacher/includes/loadteacherSidebar.js')) ?>"></script>
</aside>

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Exam Timetable</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
        <li class="breadcrumb-item active">Exam Timetable</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="card">
      <div class="card-body">
        <?php if (!$hasPlanner): ?>
          <div class="alert alert-warning mb-0">Timetable is not configured yet. Ask an administrator to set up the planner.</div>
        <?php elseif (!$slots): ?>
          <div class="alert alert-info mb-0">No timetable slots assigned to you yet.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th>Day</th>
                  <th>Period</th>
                  <th>Class</th>
                  <th>Subject</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($slots as $row): ?>
                  <tr>
                    <td><?= e((string)$row['day_label']) ?></td>
                    <td><?= e((string)$row['period_no']) ?></td>
                    <td><?= e((string)$row['standard']) ?>-<?= e((string)$row['section']) ?></td>
                    <td><?= e((string)$row['subject_name']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/teacher_footer.php'; ?>
