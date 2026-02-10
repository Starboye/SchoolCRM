<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Marks Control';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = trim($_POST['id'] ?? '');
        $testName = trim($_POST['testName'] ?? 'Term 1');
        $date = $_POST['date'] ?? date('Y-m-d');
        $total = (int)($_POST['totalMarks'] ?? 100);
        $english = (int)($_POST['english'] ?? 0);
        $tamil = (int)($_POST['tamil'] ?? 0);
        $maths = (int)($_POST['maths'] ?? 0);
        $science = (int)($_POST['science'] ?? 0);
        $social = (int)($_POST['social'] ?? 0);
        $grandTotal = (string)($english + $tamil + $maths + $science + $social);

        $existsStmt = mysqli_prepare($db, 'SELECT id FROM marks_new WHERE id=? AND testName=? LIMIT 1');
        mysqli_stmt_bind_param($existsStmt, 'ss', $id, $testName);
        mysqli_stmt_execute($existsStmt);
        mysqli_stmt_store_result($existsStmt);

        if (mysqli_stmt_num_rows($existsStmt) > 0) {
            $stmt = mysqli_prepare($db, 'UPDATE marks_new SET date=?, totalMarks=?, english=?, tamil=?, maths=?, science=?, social=?, grandTotal=? WHERE id=? AND testName=?');
            mysqli_stmt_bind_param($stmt, 'siiiiiisss', $date, $total, $english, $tamil, $maths, $science, $social, $grandTotal, $id, $testName);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $msg = 'Marks updated.';
        } else {
            $subjectName = '0';
            $stmt = mysqli_prepare($db, 'INSERT INTO marks_new (id, subjectName, testName, date, grandTotal, totalMarks, english, tamil, maths, science, social) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'sssssiiiiii', $id, $subjectName, $testName, $date, $grandTotal, $total, $english, $tamil, $maths, $science, $social);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $msg = 'Marks added.';
        }

        mysqli_stmt_close($existsStmt);
    }

    if ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        $testName = $_POST['testName'] ?? '';
        $stmt = mysqli_prepare($db, 'DELETE FROM marks_new WHERE id=? AND testName=?');
        mysqli_stmt_bind_param($stmt, 'ss', $id, $testName);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $msg = 'Marks deleted.';
    }
}

$students = mysqli_query($db, 'SELECT id, name, standard, section FROM student_info ORDER BY standard, section, name');
$marks = mysqli_query($db, 'SELECT m.*, s.name FROM marks_new m LEFT JOIN student_info s ON s.id=m.id ORDER BY m.date DESC LIMIT 300');

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main id="main" class="main">
  <div class="pagetitle"><h1>Marks Management</h1></div>
  <?php if ($msg): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>

  <div class="card"><div class="card-body">
    <h5 class="card-title">Add / Update Marks</h5>
    <form method="post" class="row g-2">
      <input type="hidden" name="action" value="save">
      <div class="col-md-3">
        <select class="form-control" name="id" required>
          <option value="">Select Student</option>
          <?php while ($s = mysqli_fetch_assoc($students)): ?>
            <option value="<?= e($s['id']) ?>"><?= e($s['name'] . ' (' . $s['id'] . ') - ' . $s['standard'] . $s['section']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-2"><input class="form-control" name="testName" value="Term 1" placeholder="Test"></div>
      <div class="col-md-2"><input type="date" class="form-control" name="date" value="<?= e(date('Y-m-d')) ?>"></div>
      <div class="col-md-1"><input type="number" class="form-control" name="totalMarks" value="100"></div>
      <div class="col-md-1"><input type="number" class="form-control" name="english" placeholder="Eng"></div>
      <div class="col-md-1"><input type="number" class="form-control" name="tamil" placeholder="Tam"></div>
      <div class="col-md-1"><input type="number" class="form-control" name="maths" placeholder="Math"></div>
      <div class="col-md-1"><input type="number" class="form-control" name="science" placeholder="Sci"></div>
      <div class="col-md-1"><input type="number" class="form-control" name="social" placeholder="Soc"></div>
      <div class="col-md-2"><button class="btn btn-primary w-100">Save</button></div>
    </form>
  </div></div>

  <div class="card"><div class="card-body">
    <h5 class="card-title">Marks Records</h5>
    <div class="table-responsive">
      <table class="table table-sm table-striped">
        <thead><tr><th>Student</th><th>Test</th><th>Date</th><th>Eng</th><th>Tam</th><th>Math</th><th>Sci</th><th>Soc</th><th>Total</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while ($m = mysqli_fetch_assoc($marks)): ?>
          <tr>
            <td><?= e(($m['name'] ?? 'Unknown') . ' (' . $m['id'] . ')') ?></td>
            <td><?= e($m['testName']) ?></td>
            <td><?= e($m['date']) ?></td>
            <td><?= e((string)$m['english']) ?></td>
            <td><?= e((string)$m['tamil']) ?></td>
            <td><?= e((string)$m['maths']) ?></td>
            <td><?= e((string)$m['science']) ?></td>
            <td><?= e((string)$m['social']) ?></td>
            <td><?= e((string)$m['grandTotal']) ?></td>
            <td>
              <form method="post" onsubmit="return confirm('Delete marks record?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= e($m['id']) ?>">
                <input type="hidden" name="testName" value="<?= e($m['testName']) ?>">
                <button class="btn btn-sm btn-outline-danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div></div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
