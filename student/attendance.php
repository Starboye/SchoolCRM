<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/auth_student.php';
require_student();

$studentId = (int)$_SESSION['user_id'];
$q = db()->prepare("SELECT name, standard, section FROM student_info WHERE id = :id LIMIT 1");
$q->execute([':id' => $studentId]);
$st = $q->fetch();
if (!$st) { http_response_code(404); echo "Student profile not found."; exit; }

$from = sanitize_date($_GET['from'] ?? date('Y-m-01'));
$to   = sanitize_date($_GET['to']   ?? date('Y-m-t'));

$sql = "SELECT date, status FROM attendance WHERE id = :id AND date BETWEEN :f AND :t ORDER BY date ASC";
$stmt = db()->prepare($sql);
$stmt->execute([':id' => $studentId, ':f' => $from, ':t' => $to]);
$rows = $stmt->fetchAll();

$total = count($rows);
$present = 0;
foreach ($rows as $r) { $present += ((int)$r['status'] === 1) ? 1 : 0; }
$percent = $total ? round(($present / $total) * 100, 2) : 0.0;

render_header('Student • Attendance');
?>
<div class="container-fluid p-3">
  <div class="d-flex flex-wrap align-items-end gap-2 mb-3">
    <h5 class="mb-0">Attendance</h5>
    <span class="text-muted"><?= htmlspecialchars($st['name']) ?> (<?= htmlspecialchars((string)$st['standard']) . "-" . htmlspecialchars($st['section']) ?>)</span>
  </div>

  <form class="row g-2 mb-3" method="get">
    <div class="col-auto">
      <label class="form-label">From</label>
      <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>">
    </div>
    <div class="col-auto">
      <label class="form-label">To</label>
      <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>">
    </div>
    <div class="col-auto d-flex align-items-end">
      <button class="btn btn-primary">Apply</button>
    </div>
  </form>

  <div class="alert alert-secondary">
    <strong>Present:</strong> <?= $present ?>/<?= $total ?> (<?= $percent ?>%)
  </div>

  <div class="table-responsive">
    <table class="table table-sm table-striped">
      <thead><tr><th>Date</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['date']) ?></td>
            <td>
              <?php if ((int)$r['status'] === 1): ?>
                <span class="badge bg-success">Present</span>
              <?php else: ?>
                <span class="badge bg-danger">Absent</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php render_footer(); ?>
