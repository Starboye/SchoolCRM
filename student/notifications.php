<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/auth_student.php';
require_student();

$studentId = (string)$_SESSION['user_id'];
$stmt = db()->prepare("SELECT notification, sentBy, date, time, status
                       FROM notification
                       WHERE id = :id
                       ORDER BY date DESC, time DESC");
$stmt->execute([':id' => $studentId]);
$list = $stmt->fetchAll();

render_header('Student • Notifications');
?>
<div class="container-fluid p-3">
  <h5 class="mb-3">Notifications</h5>
  <?php if (!$list): ?>
    <div class="alert alert-info">No notifications.</div>
  <?php else: ?>
    <div class="list-group">
      <?php foreach ($list as $n): ?>
        <div class="list-group-item">
          <div class="d-flex justify-content-between">
            <strong><?= htmlspecialchars($n['notification']) ?></strong>
            <span class="text-muted small"><?= htmlspecialchars($n['date'] . ' ' . $n['time']) ?></span>
          </div>
          <div class="small text-muted">From: <?= htmlspecialchars($n['sentBy']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php render_footer(); ?>
