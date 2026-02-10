<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Attendance Control';
$msg = '';

$date = $_GET['date'] ?? date('Y-m-d');
$standard = $_GET['standard'] ?? '';
$section = $_GET['section'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid = $_POST['id'] ?? '';
    $date = $_POST['date'] ?? date('Y-m-d');
    $morning = isset($_POST['morning']) ? 1 : 0;
    $afternoon = isset($_POST['afternoon']) ? 1 : 0;
    $evening = isset($_POST['evening']) ? 1 : 0;
    $status = ($morning || $afternoon || $evening) ? 1 : 0;

    $existsStmt = mysqli_prepare($db, 'SELECT id FROM attendance WHERE id=? AND date=? LIMIT 1');
    mysqli_stmt_bind_param($existsStmt, 'ss', $sid, $date);
    mysqli_stmt_execute($existsStmt);
    mysqli_stmt_store_result($existsStmt);

    if (mysqli_stmt_num_rows($existsStmt) > 0) {
        $stmt = mysqli_prepare($db, 'UPDATE attendance SET morning=?, afternoon=?, evening=?, status=?, teacher_id=?, markedBy=? WHERE id=? AND date=?');
        $adminId = (string)$_SESSION['id'];
        $adminName = (string)$_SESSION['name'];
        mysqli_stmt_bind_param($stmt, 'iiiissss', $morning, $afternoon, $evening, $status, $adminId, $adminName, $sid, $date);
    } else {
        $nameQ = mysqli_prepare($db, 'SELECT name FROM student_info WHERE id=? LIMIT 1');
        mysqli_stmt_bind_param($nameQ, 's', $sid);
        mysqli_stmt_execute($nameQ);
        $nameRes = mysqli_stmt_get_result($nameQ);
        $nameRow = mysqli_fetch_assoc($nameRes);
        $studentName = $nameRow['name'] ?? 'Student';
        mysqli_stmt_close($nameQ);

        $stmt = mysqli_prepare($db, 'INSERT INTO attendance (id, date, name, morning, afternoon, evening, status, teacher_id, markedBy) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $adminId = (string)$_SESSION['id'];
        $adminName = (string)$_SESSION['name'];
        mysqli_stmt_bind_param($stmt, 'sssiiiiss', $sid, $date, $studentName, $morning, $afternoon, $evening, $status, $adminId, $adminName);
    }

    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_stmt_close($existsStmt);
    $msg = 'Attendance updated.';
}

$sections = mysqli_query($db, 'SELECT DISTINCT standard, section FROM student_info ORDER BY standard, section');

$where = "WHERE 1=1";
if ($standard !== '') {
    $where .= " AND s.standard='" . mysqli_real_escape_string($db, $standard) . "'";
}
if ($section !== '') {
    $where .= " AND s.section='" . mysqli_real_escape_string($db, $section) . "'";
}

$list = mysqli_query($db, "SELECT s.id, s.name, s.standard, s.section, a.date, a.morning, a.afternoon, a.evening
                           FROM student_info s
                           LEFT JOIN attendance a ON a.id=s.id AND a.date='" . mysqli_real_escape_string($db, $date) . "'
                           " . $where . "
                           ORDER BY s.standard, s.section, s.name");

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main id="main" class="main">
  <div class="pagetitle"><h1>Attendance Control</h1></div>
  <?php if ($msg): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>

  <div class="card"><div class="card-body">
    <h5 class="card-title">Filter</h5>
    <form class="row g-2" method="get">
      <div class="col-md-3"><input type="date" class="form-control" name="date" value="<?= e($date) ?>"></div>
      <div class="col-md-2"><input class="form-control" name="standard" placeholder="Standard" value="<?= e($standard) ?>"></div>
      <div class="col-md-2"><input class="form-control" name="section" placeholder="Section" value="<?= e($section) ?>"></div>
      <div class="col-md-2"><button class="btn btn-primary">Apply</button></div>
    </form>
  </div></div>

  <div class="card"><div class="card-body">
    <h5 class="card-title">Update Attendance</h5>
    <div class="table-responsive">
    <table class="table table-sm table-bordered">
      <thead><tr><th>ID</th><th>Name</th><th>Class</th><th>Morning</th><th>Afternoon</th><th>Evening</th><th>Action</th></tr></thead>
      <tbody>
      <?php while ($r = mysqli_fetch_assoc($list)): ?>
        <tr>
          <form method="post">
          <td><?= e($r['id']) ?><input type="hidden" name="id" value="<?= e($r['id']) ?>"><input type="hidden" name="date" value="<?= e($date) ?>"></td>
          <td><?= e($r['name']) ?></td>
          <td><?= e((string)$r['standard'] . '-' . $r['section']) ?></td>
          <td><input type="checkbox" name="morning" <?= ((int)($r['morning'] ?? 0) === 1) ? 'checked' : '' ?>></td>
          <td><input type="checkbox" name="afternoon" <?= ((int)($r['afternoon'] ?? 0) === 1) ? 'checked' : '' ?>></td>
          <td><input type="checkbox" name="evening" <?= ((int)($r['evening'] ?? 0) === 1) ? 'checked' : '' ?>></td>
          <td><button class="btn btn-sm btn-primary">Save</button></td>
          </form>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
    </div>
  </div></div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
