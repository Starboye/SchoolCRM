<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/teacher_auth.php';

$pageTitle = 'Manage Marks';
$teacherId = (string)$_SESSION['id'];
$conn = db_mysqli();

$marks = [];
$sql = "SELECT m.id, m.testName, m.subjectName, m.marksObtained, m.totalMarks,
               si.name AS student_name, si.standard, si.section
        FROM marks m
        JOIN student_info si ON si.id = m.id
        JOIN teacher_subject_allocation tsa
          ON tsa.teacher_id = ?
         AND tsa.standard = si.standard
         AND tsa.section = si.section
         AND tsa.subject_name = m.subjectName
        ORDER BY m.testName DESC, si.standard, si.section, si.name
        LIMIT 200";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('s', $teacherId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $marks[] = $row;
    }
    $stmt->close();
}

include __DIR__ . '/includes/teacher_header.php';
?>
<aside id="sidebar" class="sidebar">
  <div id="sidebar-container"></div>
  <script src="<?= e(app_url('teacher/includes/loadteacherSidebar.js')) ?>"></script>
</aside>

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Manage Marks</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
        <li class="breadcrumb-item active">Manage Marks</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <p class="mb-0 text-muted">Recent marks for your classes.</p>
          <a href="add_marks.php" class="btn btn-primary btn-sm">Enter Marks</a>
        </div>
        <?php if (!$marks): ?>
          <div class="alert alert-info mb-0">No marks found yet. Use <a href="add_marks.php">Enter Marks</a> to add results.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th>Student</th>
                  <th>Class</th>
                  <th>Test</th>
                  <th>Subject</th>
                  <th>Marks</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($marks as $row): ?>
                  <tr>
                    <td><?= e((string)$row['student_name']) ?></td>
                    <td><?= e((string)$row['standard']) ?>-<?= e((string)$row['section']) ?></td>
                    <td><?= e((string)$row['testName']) ?></td>
                    <td><?= e((string)$row['subjectName']) ?></td>
                    <td><?= e((string)$row['marksObtained']) ?> / <?= e((string)$row['totalMarks']) ?></td>
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
