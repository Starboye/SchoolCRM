<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/fee_helpers.php';

require_permission($db, 'can_manage_fees');

$pageTitle = 'Fee Structure';
$msg = '';
$msgType = 'info';
$academicYear = trim((string)($_GET['year'] ?? $_POST['academic_year'] ?? fee_current_academic_year()));
$standard = (int)($_GET['standard'] ?? $_POST['standard'] ?? 0);
$section = trim((string)($_GET['section'] ?? $_POST['section'] ?? ''));

if (!fee_tables_ready($db)) {
    $pageTitle = 'Fee Structure';
    include __DIR__ . '/includes/header.php';
    include __DIR__ . '/includes/sidebar.php';
    ?>
    <main id="main" class="main">
      <div class="pagetitle"><h1>Fee Structure</h1></div>
      <div class="alert alert-warning">
        Fee tables are not installed. Import <code>admin/schema/fee_structures.sql</code> into the <code>asimos</code> database, then reload this page.
      </div>
    </main>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

$adminId = (string)$_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'save_structure') {
        $standard = (int)($_POST['standard'] ?? 0);
        $section = trim((string)($_POST['section'] ?? ''));
        $termLabel = trim((string)($_POST['term_label'] ?? ''));
        $termFee = (float)($_POST['term_fee'] ?? 0);
        $specialFee = (float)($_POST['special_fee'] ?? 0);
        $tuitionFee = (float)($_POST['tuition_fee'] ?? 0);
        $labFee = (float)($_POST['lab_fee'] ?? 0);
        $totalFee = fee_compute_total($termFee, $specialFee, $tuitionFee, $labFee);
        $structureId = (int)($_POST['structure_id'] ?? 0);

        if ($standard <= 0 || $section === '' || $termLabel === '') {
            $msg = 'Class, section, and term are required.';
            $msgType = 'danger';
        } else {
            if ($structureId > 0) {
                $stmt = $db->prepare(
                    'UPDATE fee_structures SET standard=?, section=?, term_label=?, term_fee=?, special_fee=?,
                     tuition_fee=?, lab_fee=?, total_fee=?, academic_year=?, updated_by=? WHERE id=?'
                );
                $stmt->bind_param('issddddsssi', $standard, $section, $termLabel, $termFee, $specialFee, $tuitionFee, $labFee, $totalFee, $academicYear, $adminId, $structureId);
            } else {
                $stmt = $db->prepare(
                    'INSERT INTO fee_structures (standard, section, term_label, term_fee, special_fee, tuition_fee, lab_fee, total_fee, academic_year, updated_by)
                     VALUES (?,?,?,?,?,?,?,?,?,?)'
                );
                $stmt->bind_param('issddddsss', $standard, $section, $termLabel, $termFee, $specialFee, $tuitionFee, $labFee, $totalFee, $academicYear, $adminId);
            }
            if ($stmt->execute()) {
                fee_init_class_student_status($db, $standard, $section, $academicYear, $adminId);
                log_audit($db, 'fees', 'save_structure', 'fee_structures', (string)($structureId ?: $stmt->insert_id), null, $_POST);
                $msg = $structureId > 0 ? 'Fee structure updated.' : 'Fee structure added.';
                $msgType = 'success';
            } else {
                $msg = 'Could not save fee structure. It may already exist for this class and term.';
                $msgType = 'danger';
            }
            $stmt->close();
        }
    }

    if ($action === 'delete_structure') {
        $structureId = (int)($_POST['structure_id'] ?? 0);
        if ($structureId > 0) {
            $stmt = $db->prepare('DELETE FROM fee_structures WHERE id = ?');
            $stmt->bind_param('i', $structureId);
            $stmt->execute();
            $stmt->close();
            log_audit($db, 'fees', 'delete_structure', 'fee_structures', (string)$structureId, null, null);
            $msg = 'Fee structure removed.';
            $msgType = 'success';
        }
    }

    if ($action === 'update_payment') {
        $studentId = trim((string)($_POST['student_id'] ?? ''));
        $structureId = (int)($_POST['fee_structure_id'] ?? 0);
        $status = (string)($_POST['status'] ?? 'unpaid');
        if (!in_array($status, ['paid', 'unpaid', 'partial'], true)) {
            $status = 'unpaid';
        }
        $amountPaid = (float)($_POST['amount_paid'] ?? 0);
        $paidOn = trim((string)($_POST['paid_on'] ?? ''));
        $paidOnVal = $paidOn !== '' ? $paidOn : null;

        if ($studentId !== '' && $structureId > 0) {
            if ($paidOnVal === null) {
                $stmt = $db->prepare(
                    'INSERT INTO student_fee_status (student_id, fee_structure_id, status, amount_paid, paid_on, updated_by)
                     VALUES (?,?,?,?,NULL,?)
                     ON DUPLICATE KEY UPDATE status=VALUES(status), amount_paid=VALUES(amount_paid),
                     paid_on=NULL, updated_by=VALUES(updated_by)'
                );
                $stmt->bind_param('sisds', $studentId, $structureId, $status, $amountPaid, $adminId);
            } else {
                $stmt = $db->prepare(
                    'INSERT INTO student_fee_status (student_id, fee_structure_id, status, amount_paid, paid_on, updated_by)
                     VALUES (?,?,?,?,?,?)
                     ON DUPLICATE KEY UPDATE status=VALUES(status), amount_paid=VALUES(amount_paid),
                     paid_on=VALUES(paid_on), updated_by=VALUES(updated_by)'
                );
                $stmt->bind_param('sisdss', $studentId, $structureId, $status, $amountPaid, $paidOnVal, $adminId);
            }
            $stmt->execute();
            $stmt->close();
            log_audit($db, 'fees', 'update_payment', 'student_fee_status', $studentId . ':' . $structureId, null, $_POST);
            $msg = 'Payment status updated.';
            $msgType = 'success';
        }
    }

    if ($action === 'init_class') {
        $standard = (int)($_POST['standard'] ?? 0);
        $section = trim((string)($_POST['section'] ?? ''));
        if ($standard > 0 && $section !== '') {
            $n = fee_init_class_student_status($db, $standard, $section, $academicYear, $adminId);
            $msg = "Initialized fee records for {$n} student(s).";
            $msgType = 'success';
        }
    }
}

$classes = [];
if ($res = mysqli_query($db, 'SELECT DISTINCT standard, section FROM student_info ORDER BY standard, section')) {
    while ($row = mysqli_fetch_assoc($res)) {
        $classes[] = $row;
    }
}

$structures = ($standard > 0 && $section !== '')
    ? fee_structures_for_class($db, $standard, $section, $academicYear)
    : [];

$students = [];
if ($standard > 0 && $section !== '') {
    $stmt = $db->prepare('SELECT id, name FROM student_info WHERE standard = ? AND section = ? ORDER BY name');
    $stmt->bind_param('is', $standard, $section);
    $stmt->execute();
    $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$editId = (int)($_GET['edit'] ?? 0);
$editRow = null;
if ($editId > 0) {
    $stmt = $db->prepare('SELECT * FROM fee_structures WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $editRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($editRow) {
        $standard = (int)$editRow['standard'];
        $section = (string)$editRow['section'];
    }
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main id="main" class="main">
  <div class="pagetitle">
    <h1>Fee Structure</h1>
    <p class="text-muted mb-0">Only administrators can edit fees. Students see this data on their Fee Status page.</p>
  </div>

  <?php if ($msg !== ''): ?>
    <div class="alert alert-<?= e($msgType) ?>"><?= e($msg) ?></div>
  <?php endif; ?>

  <div class="card mb-3">
    <div class="card-body">
      <h5 class="card-title">Select class</h5>
      <form method="get" class="row g-2 align-items-end">
        <div class="col-md-2">
          <label class="form-label">Academic year</label>
          <input type="text" name="year" class="form-control" value="<?= e($academicYear) ?>" placeholder="2025-26">
        </div>
        <div class="col-md-2">
          <label class="form-label">Standard</label>
          <input type="number" name="standard" class="form-control" value="<?= $standard > 0 ? e((string)$standard) : '' ?>" required min="1">
        </div>
        <div class="col-md-2">
          <label class="form-label">Section</label>
          <input type="text" name="section" class="form-control" value="<?= e($section) ?>" required maxlength="2">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary">Load</button>
        </div>
      </form>
      <?php if ($classes): ?>
        <?php $quickPick = array_slice($classes, 0, 8); ?>
        <p class="small text-muted mt-2 mb-0">Quick pick:
          <?php foreach ($quickPick as $i => $c): ?>
            <a href="?year=<?= e(urlencode($academicYear)) ?>&standard=<?= e((string)$c['standard']) ?>&section=<?= e((string)$c['section']) ?>">
              <?= e((string)$c['standard']) ?>-<?= e((string)$c['section']) ?>
            </a><?= $i < count($quickPick) - 1 ? ', ' : '' ?>
          <?php endforeach; ?>
        </p>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($standard > 0 && $section !== ''): ?>

  <div class="row">
    <div class="col-lg-5">
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title"><?= $editRow ? 'Edit' : 'Add' ?> fee term</h5>
          <form method="post" class="row g-2">
            <input type="hidden" name="action" value="save_structure">
            <input type="hidden" name="academic_year" value="<?= e($academicYear) ?>">
            <input type="hidden" name="standard" value="<?= $standard ?>">
            <input type="hidden" name="section" value="<?= e($section) ?>">
            <?php if ($editRow): ?>
              <input type="hidden" name="structure_id" value="<?= (int)$editRow['id'] ?>">
            <?php endif; ?>
            <div class="col-12">
              <label class="form-label">Term</label>
              <input type="text" name="term_label" class="form-control" required
                     value="<?= e((string)($editRow['term_label'] ?? '')) ?>" placeholder="Term 1">
            </div>
            <div class="col-6">
              <label class="form-label">Term fee</label>
              <input type="number" step="0.01" min="0" name="term_fee" class="form-control" value="<?= e((string)($editRow['term_fee'] ?? '0')) ?>">
            </div>
            <div class="col-6">
              <label class="form-label">Special fee</label>
              <input type="number" step="0.01" min="0" name="special_fee" class="form-control" value="<?= e((string)($editRow['special_fee'] ?? '0')) ?>">
            </div>
            <div class="col-6">
              <label class="form-label">Tuition fee</label>
              <input type="number" step="0.01" min="0" name="tuition_fee" class="form-control" value="<?= e((string)($editRow['tuition_fee'] ?? '0')) ?>">
            </div>
            <div class="col-6">
              <label class="form-label">Lab fee</label>
              <input type="number" step="0.01" min="0" name="lab_fee" class="form-control" value="<?= e((string)($editRow['lab_fee'] ?? '0')) ?>">
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-primary"><?= $editRow ? 'Update' : 'Save' ?> structure</button>
              <?php if ($editRow): ?>
                <a href="?year=<?= e(urlencode($academicYear)) ?>&standard=<?= $standard ?>&section=<?= e(urlencode($section)) ?>" class="btn btn-outline-secondary">Cancel</a>
              <?php endif; ?>
            </div>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Initialize students</h5>
          <p class="small text-muted">Creates unpaid fee records for every student in class <?= e((string)$standard) ?>-<?= e($section) ?>.</p>
          <form method="post">
            <input type="hidden" name="action" value="init_class">
            <input type="hidden" name="academic_year" value="<?= e($academicYear) ?>">
            <input type="hidden" name="standard" value="<?= $standard ?>">
            <input type="hidden" name="section" value="<?= e($section) ?>">
            <button type="submit" class="btn btn-outline-primary btn-sm">Sync all students</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title">Fee structure — Class <?= e((string)$standard) ?>-<?= e($section) ?> (<?= e($academicYear) ?>)</h5>
          <?php if (!$structures): ?>
            <div class="alert alert-info mb-0">No fee terms defined yet. Add Term 1, Term 2, etc. using the form on the left.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-bordered table-sm align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Term</th>
                    <th>Term</th>
                    <th>Special</th>
                    <th>Tuition</th>
                    <th>Lab</th>
                    <th>Total</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($structures as $fs): ?>
                    <tr>
                      <td><?= e((string)$fs['term_label']) ?></td>
                      <td><?= e(fee_format_amount($fs['term_fee'])) ?></td>
                      <td><?= e(fee_format_amount($fs['special_fee'])) ?></td>
                      <td><?= e(fee_format_amount($fs['tuition_fee'])) ?></td>
                      <td><?= e(fee_format_amount($fs['lab_fee'])) ?></td>
                      <td><strong><?= e(fee_format_amount($fs['total_fee'])) ?></strong></td>
                      <td class="text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="?year=<?= e(urlencode($academicYear)) ?>&standard=<?= $standard ?>&section=<?= e(urlencode($section)) ?>&edit=<?= (int)$fs['id'] ?>">Edit</a>
                        <form method="post" class="d-inline" onsubmit="return confirm('Delete this fee term?');">
                          <input type="hidden" name="action" value="delete_structure">
                          <input type="hidden" name="structure_id" value="<?= (int)$fs['id'] ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($structures && $students): ?>
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Student payment status</h5>
          <form method="post" class="row g-2 mb-3">
            <input type="hidden" name="action" value="update_payment">
            <div class="col-md-4">
              <label class="form-label">Student</label>
              <select name="student_id" class="form-select" required>
                <option value="">Select</option>
                <?php foreach ($students as $s): ?>
                  <option value="<?= e((string)$s['id']) ?>"><?= e((string)$s['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Term</label>
              <select name="fee_structure_id" class="form-select" required>
                <?php foreach ($structures as $fs): ?>
                  <option value="<?= (int)$fs['id'] ?>"><?= e((string)$fs['term_label']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <option value="paid">Paid</option>
                <option value="unpaid">Unpaid</option>
                <option value="partial">Partial</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Amount paid</label>
              <input type="number" step="0.01" min="0" name="amount_paid" class="form-control" value="0">
            </div>
            <div class="col-md-3">
              <label class="form-label">Paid on</label>
              <input type="date" name="paid_on" class="form-control">
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <button type="submit" class="btn btn-success w-100">Update</button>
            </div>
          </form>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <?php endif; ?>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
