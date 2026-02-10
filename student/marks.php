<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/auth_student.php';
require_student();

$studentId = (string)$_SESSION['user_id'];
$testName = trim($_GET['test'] ?? 'Term 1');

// From existing `marks` (per your dump). Also supports `marks_new` summary rows.
$stmt = db()->prepare("SELECT subjectName, testName, date, marksObtained, totalMarks
                       FROM marks
                       WHERE id = :id AND testName = :t
                       ORDER BY subjectName ASC");
$stmt->execute([':id' => $studentId, ':t' => $testName]);
$list = $stmt->fetchAll();

$sum = 0; $total = 0;
foreach ($list as $m) {
  $sum += (int)($m['marksObtained'] ?? 0);
  $total += (int)($m['totalMarks'] ?? 0);
}
$percent = $total ? round(($sum / $total) * 100, 2) : 0.0;

render_header('Student • Marks');
?>
<div class="container-fluid p-3">
  <div class="d-flex flex-wrap align-items-end gap-2 mb-3">
    <h5 class="mb-0">Marks</h5>
  </div>

  <form class="row g-2 mb-3" method="get">
    <div class="col-auto">
      <label class="form-label">Test</label>
      <input class="form-control" name="test" value="<?= htmlspecialchars($testName) ?>">
    </div>
    <div class="col-auto d-flex align-items-end">
      <button class="btn btn-primary">View</button>
    </div>
  </form>

  <div class="alert alert-secondary">
    <strong>Total:</strong> <?= $sum ?>/<?= $total ?> (<?= $percent ?>%)
  </div>

  <?php if (!$list): ?>
    <div class="alert alert-info">No marks found for this test.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-striped table-sm">
        <thead><tr><th>Subject</th><th>Marks</th><th>Total</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($list as $m): ?>
          <tr>
            <td><?= htmlspecialchars($m['subjectName']) ?></td>
            <td><?= htmlspecialchars($m['marksObtained'] ?? '') ?></td>
            <td><?= htmlspecialchars($m['totalMarks'] ?? '') ?></td>
            <td><?= htmlspecialchars($m['date'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php render_footer(); ?>
