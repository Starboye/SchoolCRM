<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/teacher_auth.php';
require_once dirname(__DIR__) . '/includes/timetable_helpers.php';

$pageTitle = 'Class Timetable';
$teacherId = (string)$_SESSION['id'];
$conn = db_mysqli();
$academicYear = trim((string)($_GET['year'] ?? $_POST['academic_year'] ?? tt_current_academic_year()));
$standard = (int)($_GET['standard'] ?? $_POST['standard'] ?? 0);
$section = trim((string)($_GET['section'] ?? $_POST['section'] ?? ''));
$msg = '';
$msgType = 'info';
$maxPeriods = 8;
$dayLabels = tt_day_labels();

if (!tt_tables_ready($conn)) {
    include __DIR__ . '/includes/teacher_header.php';
    ?>
    <aside id="sidebar" class="sidebar">
      <div id="sidebar-container"></div>
      <script src="<?= e(app_url('teacher/includes/loadteacherSidebar.js')) ?>"></script>
    </aside>
    <main id="main" class="main">
      <div class="pagetitle"><h1>Class Timetable</h1></div>
      <div class="alert alert-warning">Timetable workflow tables are not installed. Ask an administrator to import <code>admin/schema/timetable_workflow.sql</code>.</div>
    </main>
    <?php
    include __DIR__ . '/includes/teacher_footer.php';
    exit;
}

$assignedClasses = tt_classes_for_teacher($conn, $teacherId, $academicYear);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    $standard = (int)($_POST['standard'] ?? 0);
    $section = trim((string)($_POST['section'] ?? ''));

    if (!tt_is_class_teacher($conn, $teacherId, $standard, $section, $academicYear)) {
        $msg = 'You are not the class teacher for this class.';
        $msgType = 'danger';
    } elseif ($action === 'save_grid') {
        if (!tt_teacher_can_edit($conn, $teacherId, $standard, $section, $academicYear)) {
            $msg = 'This timetable is locked. Contact the administrator to make changes.';
            $msgType = 'danger';
        } else {
            $cells = [];
            $gridPost = $_POST['grid'] ?? [];
            if (is_array($gridPost)) {
                foreach ($gridPost as $period => $days) {
                    if (!is_array($days)) {
                        continue;
                    }
                    foreach ($days as $day => $subject) {
                        $cells[(int)$period][(int)$day] = (string)$subject;
                    }
                }
            }
            $saved = tt_save_class_grid($conn, $standard, $section, $teacherId, $cells);
            tt_upsert_class_status($conn, $standard, $section, $academicYear, 'draft', $teacherId);
            $msg = "Timetable saved ({$saved} slot(s)). Submit for admin approval when ready.";
            $msgType = 'success';
        }
    } elseif ($action === 'submit_approval') {
        if (!tt_teacher_can_edit($conn, $teacherId, $standard, $section, $academicYear)) {
            $msg = 'This timetable cannot be submitted in its current state.';
            $msgType = 'danger';
        } else {
            $grid = tt_grid_for_class($conn, $standard, $section, false, $academicYear);
            if ($grid['maxPeriod'] === 0) {
                $msg = 'Add at least one period before submitting for approval.';
                $msgType = 'warning';
            } else {
                tt_upsert_class_status($conn, $standard, $section, $academicYear, 'pending', $teacherId);
                $entityId = tt_entity_id($standard, $section, $academicYear);
                $payload = [
                    'standard' => $standard,
                    'section' => $section,
                    'academic_year' => $academicYear,
                    'slots' => $grid['maxPeriod'],
                ];
                $stmt = $conn->prepare(
                    'INSERT INTO approval_requests (module, action, entity_type, entity_id, payload_json, requested_by)
                     VALUES ("timetable", "submit", "class_timetable", ?, ?, ?)'
                );
                $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE);
                $stmt->bind_param('sss', $entityId, $payloadJson, $teacherId);
                $stmt->execute();
                $stmt->close();
                $msg = 'Timetable submitted for admin approval. You cannot edit it until it is reviewed.';
                $msgType = 'success';
            }
        }
    }
}

$status = ($standard > 0 && $section !== '') ? tt_get_status($conn, $standard, $section, $academicYear) : '';
$reviewNote = '';
if ($standard > 0 && $section !== '') {
    $noteStmt = $conn->prepare(
        'SELECT review_note FROM class_timetables WHERE standard = ? AND section = ? AND academic_year = ? LIMIT 1'
    );
    $noteStmt->bind_param('iss', $standard, $section, $academicYear);
    $noteStmt->execute();
    $noteRow = $noteStmt->get_result()->fetch_assoc();
    $noteStmt->close();
    $reviewNote = (string)($noteRow['review_note'] ?? '');
}
$canEdit = ($standard > 0 && $section !== '') && tt_teacher_can_edit($conn, $teacherId, $standard, $section, $academicYear);
$gridData = ($standard > 0 && $section !== '')
    ? tt_grid_for_class($conn, $standard, $section, false, $academicYear)
    : ['grid' => [], 'maxPeriod' => 0];
$grid = $gridData['grid'];
$displayPeriods = max($gridData['maxPeriod'], $maxPeriods);

include __DIR__ . '/includes/teacher_header.php';
?>
<aside id="sidebar" class="sidebar">
  <div id="sidebar-container"></div>
  <script src="<?= e(app_url('teacher/includes/loadteacherSidebar.js')) ?>"></script>
</aside>

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Class Timetable</h1>
    <p class="text-muted mb-0">Build your class timetable and submit it for admin approval. Once approved, only administrators can change it.</p>
  </div>

  <?php if ($msg !== ''): ?>
    <div class="alert alert-<?= e($msgType) ?>"><?= e($msg) ?></div>
  <?php endif; ?>

  <div class="card mb-3">
    <div class="card-body">
      <h5 class="card-title">Your classes (<?= e($academicYear) ?>)</h5>
      <?php if (!$assignedClasses): ?>
        <div class="alert alert-info mb-0">You are not assigned as a class teacher yet. Ask an administrator to assign you in the Planner.</div>
      <?php else: ?>
        <div class="d-flex flex-wrap gap-2">
          <?php foreach ($assignedClasses as $cls): ?>
            <?php $badge = tt_status_badge_class((string)$cls['status']); ?>
            <a class="btn btn-outline-primary"
               href="?year=<?= e(urlencode($academicYear)) ?>&standard=<?= (int)$cls['standard'] ?>&section=<?= e(urlencode((string)$cls['section'])) ?>">
              Class <?= e((string)$cls['standard']) ?>-<?= e((string)$cls['section']) ?>
              <span class="badge bg-<?= e($badge) ?> ms-1"><?= e(ucfirst((string)$cls['status'])) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($standard > 0 && $section !== '' && tt_is_class_teacher($conn, $teacherId, $standard, $section, $academicYear)): ?>
  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="card-title mb-0">Class <?= e((string)$standard) ?>-<?= e($section) ?> — <?= e($academicYear) ?></h5>
        <span class="badge bg-<?= e(tt_status_badge_class($status)) ?>"><?= e(ucfirst($status ?: 'draft')) ?></span>
      </div>

      <?php if ($status === 'pending'): ?>
        <div class="alert alert-warning">Awaiting admin approval. Editing is disabled.</div>
      <?php elseif ($status === 'approved'): ?>
        <div class="alert alert-success">Approved and published to students. Contact admin for any changes.</div>
      <?php elseif ($status === 'rejected'): ?>
        <div class="alert alert-danger">Rejected<?= $reviewNote !== '' ? ': ' . e($reviewNote) : '. Please revise and resubmit.' ?></div>
      <?php endif; ?>

      <form method="post">
        <input type="hidden" name="action" value="save_grid">
        <input type="hidden" name="academic_year" value="<?= e($academicYear) ?>">
        <input type="hidden" name="standard" value="<?= $standard ?>">
        <input type="hidden" name="section" value="<?= e($section) ?>">

        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead class="table-light">
              <tr>
                <th>Period</th>
                <?php foreach ($dayLabels as $label): ?>
                  <th><?= e($label) ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php for ($p = 1; $p <= $displayPeriods; $p++): ?>
              <tr>
                <td><?= $p ?></td>
                <?php for ($d = 1; $d <= 5; $d++): ?>
                  <td>
                    <input type="text" class="form-control form-control-sm"
                           name="grid[<?= $p ?>][<?= $d ?>]"
                           value="<?= e($grid[$p][$d] ?? '') ?>"
                           <?= $canEdit ? '' : 'readonly' ?>
                           placeholder="Subject">
                  </td>
                <?php endfor; ?>
              </tr>
              <?php endfor; ?>
            </tbody>
          </table>
        </div>

        <div class="d-flex gap-2">
          <?php if ($canEdit): ?>
            <button type="submit" class="btn btn-primary">Save draft</button>
          <?php endif; ?>
        </div>
      </form>

      <?php if ($canEdit && $gridData['maxPeriod'] > 0): ?>
        <form method="post" class="mt-3" onsubmit="return confirm('Submit this timetable for admin approval? You will not be able to edit it until reviewed.');">
          <input type="hidden" name="action" value="submit_approval">
          <input type="hidden" name="academic_year" value="<?= e($academicYear) ?>">
          <input type="hidden" name="standard" value="<?= $standard ?>">
          <input type="hidden" name="section" value="<?= e($section) ?>">
          <button type="submit" class="btn btn-success">Submit for approval</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/teacher_footer.php'; ?>
