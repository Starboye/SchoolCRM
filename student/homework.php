<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/auth_student.php';
require_student();

$studentId = (int)$_SESSION['user_id'];
// Fetch student class/section from student_info
$student = db()->prepare("SELECT standard, section, name FROM student_info WHERE id = :id LIMIT 1");
$student->execute([':id' => $studentId]);
$st = $student->fetch();
if (!$st) { http_response_code(404); echo "Student profile not found."; exit; }

$today = date('Y-m-d');
$selectedDate = sanitize_date($_GET['date'] ?? $today);
$subject = trim($_GET['subject'] ?? '');

$sql = "SELECT id, subject_name, teacher_id, standard, section, date, title, description
        FROM homeworks
        WHERE standard = :std AND section = :sec AND date = :d";
$params = [':std' => (int)$st['standard'], ':sec' => $st['section'], ':d' => $selectedDate];

if ($subject !== '') {
  $sql .= " AND subject_name = :sub";
  $params[':sub'] = $subject;
}
$sql .= " ORDER BY subject_name ASC, id DESC";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$list = $stmt->fetchAll();

render_header('Student • Homework');
?>
<div class="container-fluid p-3">
  <div class="d-flex flex-wrap align-items-end gap-2 mb-3">
    <h5 class="mb-0">Homework</h5>
    <span class="text-muted">Class <?= htmlspecialchars((string)$st['standard']) . "-" . htmlspecialchars($st['section']) ?></span>
  </div>

  <form class="row g-2 mb-3" method="get">
    <div class="col-auto">
      <label class="form-label">Date</label>
      <input type="date" class="form-control" name="date" value="<?= htmlspecialchars($selectedDate) ?>">
    </div>
    <div class="col-auto">
      <label class="form-label">Subject (optional)</label>
      <input type="text" class="form-control" name="subject" placeholder="e.g. English" value="<?= htmlspecialchars($subject) ?>">
    </div>
    <div class="col-auto d-flex align-items-end">
      <button class="btn btn-primary">Filter</button>
    </div>
    <div class="col-auto d-flex align-items-end gap-2">
      <?php
        $prev = date('Y-m-d', strtotime($selectedDate . ' -1 day'));
        $next = date('Y-m-d', strtotime($selectedDate . ' +1 day'));
      ?>
      <a class="btn btn-outline-secondary" href="?date=<?= $prev ?>&subject=<?= urlencode($subject) ?>">&larr; Previous</a>
      <a class="btn btn-outline-secondary" href="?date=<?= $today ?>&subject=">Today</a>
      <a class="btn btn-outline-secondary" href="?date=<?= $next ?>&subject=<?= urlencode($subject) ?>">Next &rarr;</a>
    </div>
  </form>

  <?php if (!$list): ?>
    <div class="alert alert-info">No homework for <?= htmlspecialchars($selectedDate) ?>.</div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($list as $hw): ?>
        <div class="col-md-6">
          <div class="card shadow-sm h-100">
            <div class="card-body">
              <div class="d-flex justify-content-between">
                <h6 class="mb-1"><?= htmlspecialchars($hw['title']) ?></h6>
                <span class="badge bg-secondary"><?= htmlspecialchars($hw['subject_name']) ?></span>
              </div>
              <p class="mb-2 small text-muted">Due: <?= htmlspecialchars($hw['date']) ?></p>
              <?php if (!empty($hw['description'])): ?>
                <p class="mb-0"><?= nl2br(htmlspecialchars($hw['description'])) ?></p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php render_footer(); ?>
