<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/timetable_helpers.php';

require_permission($db, 'can_manage_planner');

$pageTitle = 'Timetable & Workload Planner';
$msg = '';
$msgType = 'info';
$academicYear = trim((string)($_GET['year'] ?? $_POST['academic_year'] ?? tt_current_academic_year()));
$workflowReady = tt_tables_ready($db);
$adminId = (string)$_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'add_slot') {
        $teacher = trim((string)($_POST['teacher_id'] ?? ''));
        $std = (int)($_POST['standard'] ?? 0);
        $sec = trim((string)($_POST['section'] ?? ''));
        $sub = trim((string)($_POST['subject_name'] ?? ''));
        $day = (int)($_POST['day_of_week'] ?? 0);
        $period = (int)($_POST['period_no'] ?? 0);

        if ($teacher === '' || $std <= 0 || $sec === '' || $sub === '' || $day < 1 || $period < 1) {
            $msg = 'All slot fields are required.';
            $msgType = 'danger';
        } else {
            $confStmt = $db->prepare(
                'SELECT id FROM timetable_slots WHERE teacher_id = ? AND day_of_week = ? AND period_no = ? LIMIT 1'
            );
            $confStmt->bind_param('sii', $teacher, $day, $period);
            $confStmt->execute();
            $conf = $confStmt->get_result()->fetch_assoc();
            $confStmt->close();

            if ($conf) {
                $msg = 'Conflict: teacher already allocated for this day/period.';
                $msgType = 'warning';
            } else {
                $stmt = $db->prepare(
                    'INSERT INTO timetable_slots (teacher_id, standard, section, subject_name, day_of_week, period_no, created_by)
                     VALUES (?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->bind_param('sissiis', $teacher, $std, $sec, $sub, $day, $period, $adminId);
                $stmt->execute();
                $stmt->close();
                log_audit($db, 'planner', 'create_slot', 'timetable_slot', null, null, ['teacher' => $teacher, 'day' => $day, 'period' => $period]);
                $msg = 'Slot added. Administrators can edit timetables at any time.';
                $msgType = 'success';
            }
        }
    }

    if ($action === 'assign_class_teacher' && $workflowReady) {
        $std = (int)($_POST['standard'] ?? 0);
        $sec = trim((string)($_POST['section'] ?? ''));
        $teacherId = trim((string)($_POST['class_teacher_id'] ?? ''));
        if ($std > 0 && $sec !== '' && $teacherId !== '') {
            $stmt = $db->prepare(
                'INSERT INTO class_teacher_assignments (standard, section, academic_year, class_teacher_id, assigned_by)
                 VALUES (?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE class_teacher_id = VALUES(class_teacher_id), assigned_by = VALUES(assigned_by), assigned_at = NOW()'
            );
            $stmt->bind_param('issss', $std, $sec, $academicYear, $teacherId, $adminId);
            $stmt->execute();
            $stmt->close();
            $msg = "Class teacher assigned for {$std}-{$sec}.";
            $msgType = 'success';
        }
    }

    if ($action === 'admin_save_grid' && $workflowReady) {
        $std = (int)($_POST['standard'] ?? 0);
        $sec = trim((string)($_POST['section'] ?? ''));
        $teacherId = trim((string)($_POST['class_teacher_id'] ?? $adminId));
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
        if ($std > 0 && $sec !== '') {
            $saved = tt_save_class_grid($db, $std, $sec, $teacherId, $cells, $adminId);
            tt_upsert_class_status($db, $std, $sec, $academicYear, 'approved', $adminId);
            $stmt = $db->prepare(
                'UPDATE class_timetables SET reviewed_by = ?, reviewed_at = NOW() WHERE standard = ? AND section = ? AND academic_year = ?'
            );
            $stmt->bind_param('ssis', $adminId, $std, $sec, $academicYear);
            $stmt->execute();
            $stmt->close();
            $msg = "Admin updated and published timetable ({$saved} slot(s)).";
            $msgType = 'success';
        }
    }
}

$teachers = mysqli_query($db, 'SELECT id, name FROM user_login WHERE access = 1 ORDER BY name');
$slots = mysqli_query($db, 'SELECT teacher_id, day_of_week, period_no, standard, section, subject_name FROM timetable_slots ORDER BY day_of_week, period_no');
$load = mysqli_query($db, 'SELECT teacher_id, COUNT(*) c FROM timetable_slots GROUP BY teacher_id ORDER BY c DESC');

$classes = [];
if ($res = mysqli_query($db, 'SELECT DISTINCT standard, section FROM student_info ORDER BY standard, section')) {
    while ($row = mysqli_fetch_assoc($res)) {
        $classes[] = $row;
    }
}

$classTeachers = [];
$pendingTimetables = [];
if ($workflowReady) {
    $ctRes = mysqli_query(
        $db,
        "SELECT cs.standard, cs.section, cs.class_teacher_id, ul.name AS teacher_name,
                COALESCE(ct.status, 'draft') AS status
         FROM class_teacher_assignments cs
         LEFT JOIN user_login ul ON ul.id = cs.class_teacher_id
         LEFT JOIN class_timetables ct ON ct.standard = cs.standard AND ct.section = cs.section AND ct.academic_year = cs.academic_year
         WHERE cs.academic_year = '" . mysqli_real_escape_string($db, $academicYear) . "'
         ORDER BY cs.standard, cs.section"
    );
    while ($row = mysqli_fetch_assoc($ctRes)) {
        $classTeachers[] = $row;
        if ($row['status'] === 'pending') {
            $pendingTimetables[] = $row;
        }
    }
}

$editStd = (int)($_GET['standard'] ?? 0);
$editSec = trim((string)($_GET['section'] ?? ''));
$editGrid = ($editStd > 0 && $editSec !== '') ? tt_grid_for_class($db, $editStd, $editSec, false, $academicYear) : ['grid' => [], 'maxPeriod' => 0];
$dayLabels = tt_day_labels();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main id="main" class="main">
  <div class="pagetitle">
    <h1>Planner</h1>
    <p class="text-muted mb-0">Assign class teachers, approve timetables, and manage slots. Admins can update any timetable at any time.</p>
  </div>

  <?php if ($msg !== ''): ?>
    <div class="alert alert-<?= e($msgType) ?>"><?= e($msg) ?></div>
  <?php endif; ?>

  <?php if (!$workflowReady): ?>
    <div class="alert alert-warning">Import <code>admin/schema/timetable_workflow.sql</code> to enable class-teacher timetable approval.</div>
  <?php endif; ?>

  <?php if ($workflowReady && $pendingTimetables): ?>
    <div class="alert alert-warning">
      <?= count($pendingTimetables) ?> timetable(s) awaiting approval.
      <a href="approvals.php" class="alert-link">Review in Approvals</a>
    </div>
  <?php endif; ?>

  <?php if ($workflowReady): ?>
  <div class="card mb-3">
    <div class="card-body">
      <h5 class="card-title">Assign class teacher (<?= e($academicYear) ?>)</h5>
      <form method="post" class="row g-2 align-items-end">
        <input type="hidden" name="action" value="assign_class_teacher">
        <input type="hidden" name="academic_year" value="<?= e($academicYear) ?>">
        <div class="col-md-2">
          <label class="form-label">Standard</label>
          <input type="number" name="standard" class="form-control" min="1" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Section</label>
          <input type="text" name="section" class="form-control" maxlength="2" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Class teacher</label>
          <select name="class_teacher_id" class="form-select" required>
            <option value="">Select teacher</option>
            <?php mysqli_data_seek($teachers, 0); while ($t = mysqli_fetch_assoc($teachers)): ?>
              <option value="<?= e($t['id']) ?>"><?= e($t['name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary">Assign</button>
        </div>
      </form>
      <?php if ($classTeachers): ?>
        <div class="table-responsive mt-3">
          <table class="table table-sm table-bordered">
            <thead><tr><th>Class</th><th>Class teacher</th><th>Timetable status</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($classTeachers as $ct): ?>
                <tr>
                  <td><?= e((string)$ct['standard']) ?>-<?= e((string)$ct['section']) ?></td>
                  <td><?= e((string)($ct['teacher_name'] ?? $ct['class_teacher_id'])) ?></td>
                  <td><span class="badge bg-<?= e(tt_status_badge_class((string)$ct['status'])) ?>"><?= e(ucfirst((string)$ct['status'])) ?></span></td>
                  <td><a href="?year=<?= e(urlencode($academicYear)) ?>&standard=<?= (int)$ct['standard'] ?>&section=<?= e(urlencode((string)$ct['section'])) ?>">Edit grid</a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($editStd > 0 && $editSec !== ''): ?>
  <div class="card mb-3">
    <div class="card-body">
      <h5 class="card-title">Edit class timetable — <?= e((string)$editStd) ?>-<?= e($editSec) ?></h5>
      <form method="post">
        <input type="hidden" name="action" value="admin_save_grid">
        <input type="hidden" name="academic_year" value="<?= e($academicYear) ?>">
        <input type="hidden" name="standard" value="<?= $editStd ?>">
        <input type="hidden" name="section" value="<?= e($editSec) ?>">
        <div class="table-responsive">
          <table class="table table-bordered table-sm">
            <thead class="table-light"><tr><th>Period</th><?php foreach ($dayLabels as $l): ?><th><?= e($l) ?></th><?php endforeach; ?></tr></thead>
            <tbody>
              <?php for ($p = 1; $p <= max(8, $editGrid['maxPeriod']); $p++): ?>
              <tr>
                <td><?= $p ?></td>
                <?php for ($d = 1; $d <= 5; $d++): ?>
                  <td><input class="form-control form-control-sm" name="grid[<?= $p ?>][<?= $d ?>]" value="<?= e($editGrid['grid'][$p][$d] ?? '') ?>"></td>
                <?php endfor; ?>
              </tr>
              <?php endfor; ?>
            </tbody>
          </table>
        </div>
        <button type="submit" class="btn btn-primary">Save as admin</button>
      </form>
    </div>
  </div>
  <?php endif; ?>
  <?php endif; ?>

  <div class="card mb-3">
    <div class="card-body">
      <h5 class="card-title">Add single slot</h5>
      <form method="post" class="row g-2">
        <input type="hidden" name="action" value="add_slot">
        <div class="col-md-3">
          <select class="form-select" name="teacher_id" required>
            <?php mysqli_data_seek($teachers, 0); while ($t = mysqli_fetch_assoc($teachers)): ?>
              <option value="<?= e($t['id']) ?>"><?= e($t['name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-1"><input class="form-control" name="standard" placeholder="Std" required></div>
        <div class="col-md-1"><input class="form-control" name="section" placeholder="Sec" required></div>
        <div class="col-md-2"><input class="form-control" name="subject_name" placeholder="Subject" required></div>
        <div class="col-md-1"><input class="form-control" name="day_of_week" placeholder="1-5" min="1" max="7" required></div>
        <div class="col-md-1"><input class="form-control" name="period_no" placeholder="Period" min="1" required></div>
        <div class="col-md-2"><button class="btn btn-primary">Add</button></div>
      </form>
      <?php if ($classes): ?>
        <?php $classPreview = array_slice($classes, 0, 10); ?>
        <p class="small text-muted mt-2 mb-0">Classes:
          <?php foreach ($classPreview as $i => $c): ?>
            <span><?= e((string)$c['standard']) ?>-<?= e((string)$c['section']) ?></span><?= $i < count($classPreview) - 1 ? ', ' : '' ?>
          <?php endforeach; ?>
        </p>
      <?php endif; ?>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">All slots</h5>
          <table class="table table-sm table-striped">
            <thead><tr><th>Teacher</th><th>Day</th><th>Period</th><th>Class</th><th>Subject</th></tr></thead>
            <tbody>
              <?php while ($s = mysqli_fetch_assoc($slots)): ?>
                <tr>
                  <td><?= e($s['teacher_id']) ?></td>
                  <td><?= e((string)$s['day_of_week']) ?></td>
                  <td><?= e((string)$s['period_no']) ?></td>
                  <td><?= e($s['standard'] . '-' . $s['section']) ?></td>
                  <td><?= e($s['subject_name']) ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Workload</h5>
          <ul class="list-group">
            <?php while ($l = mysqli_fetch_assoc($load)): ?>
              <li class="list-group-item d-flex justify-content-between">
                <span><?= e($l['teacher_id']) ?></span>
                <strong><?= e((string)$l['c']) ?></strong>
              </li>
            <?php endwhile; ?>
          </ul>
        </div>
      </div>
    </div>
  </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
