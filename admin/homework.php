<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Homework Control';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = $_POST['id'] ?? '';
        $subject = trim($_POST['subject_name'] ?? '');
        $teacherId = trim($_POST['teacher_id'] ?? '');
        $standard = (int)($_POST['standard'] ?? 0);
        $section = trim($_POST['section'] ?? '');
        $date = $_POST['date'] ?? date('Y-m-d');
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($id === '') {
            $stmt = mysqli_prepare($db, 'INSERT INTO homeworks (subject_name, teacher_id, standard, section, date, title, description) VALUES (?, ?, ?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'ssissss', $subject, $teacherId, $standard, $section, $date, $title, $description);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $msg = 'Homework added.';
        } else {
            $stmt = mysqli_prepare($db, 'UPDATE homeworks SET subject_name=?, teacher_id=?, standard=?, section=?, date=?, title=?, description=? WHERE id=?');
            mysqli_stmt_bind_param($stmt, 'ssissssi', $subject, $teacherId, $standard, $section, $date, $title, $description, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $msg = 'Homework updated.';
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = mysqli_prepare($db, 'DELETE FROM homeworks WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $msg = 'Homework deleted.';
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM homeworks WHERE id=$id LIMIT 1"));
}

$teachers = mysqli_query($db, 'SELECT id, name FROM user_login WHERE access=1 ORDER BY name');
$list = mysqli_query($db, 'SELECT h.*, u.name AS teacher_name FROM homeworks h LEFT JOIN user_login u ON u.id=h.teacher_id ORDER BY h.id DESC LIMIT 200');

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main id="main" class="main">
  <div class="pagetitle"><h1>Homework Management</h1></div>
  <?php if ($msg): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>

  <div class="card"><div class="card-body">
    <h5 class="card-title"><?= $edit ? 'Edit Homework' : 'Add Homework' ?></h5>
    <form method="post" class="row g-2">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= e((string)($edit['id'] ?? '')) ?>">
      <div class="col-md-2"><input class="form-control" name="subject_name" placeholder="Subject" value="<?= e($edit['subject_name'] ?? '') ?>" required></div>
      <div class="col-md-3">
        <select class="form-control" name="teacher_id" required>
          <option value="">Select Teacher</option>
          <?php mysqli_data_seek($teachers, 0); while ($t = mysqli_fetch_assoc($teachers)): ?>
            <option value="<?= e($t['id']) ?>" <?= (($edit['teacher_id'] ?? '') === $t['id']) ? 'selected' : '' ?>><?= e($t['name'] . ' (' . $t['id'] . ')') ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-1"><input class="form-control" type="number" name="standard" placeholder="Std" value="<?= e((string)($edit['standard'] ?? '')) ?>" required></div>
      <div class="col-md-1"><input class="form-control" name="section" placeholder="Sec" value="<?= e($edit['section'] ?? '') ?>" required></div>
      <div class="col-md-2"><input class="form-control" type="date" name="date" value="<?= e($edit['date'] ?? date('Y-m-d')) ?>" required></div>
      <div class="col-md-3"><input class="form-control" name="title" placeholder="Title" value="<?= e($edit['title'] ?? '') ?>" required></div>
      <div class="col-12"><textarea class="form-control" name="description" placeholder="Description" rows="2"><?= e($edit['description'] ?? '') ?></textarea></div>
      <div class="col-md-2"><button class="btn btn-primary w-100"><?= $edit ? 'Update' : 'Add' ?></button></div>
      <?php if ($edit): ?><div class="col-md-2"><a class="btn btn-secondary w-100" href="homework.php">Cancel</a></div><?php endif; ?>
    </form>
  </div></div>

  <div class="card"><div class="card-body">
    <h5 class="card-title">Homework List</h5>
    <div class="table-responsive">
      <table class="table table-sm table-striped">
        <thead><tr><th>ID</th><th>Title</th><th>Subject</th><th>Class</th><th>Date</th><th>Teacher</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while ($r = mysqli_fetch_assoc($list)): ?>
          <tr>
            <td><?= e((string)$r['id']) ?></td>
            <td><?= e($r['title']) ?></td>
            <td><?= e($r['subject_name']) ?></td>
            <td><?= e((string)$r['standard'] . '-' . $r['section']) ?></td>
            <td><?= e($r['date']) ?></td>
            <td><?= e($r['teacher_name'] ?? $r['teacher_id']) ?></td>
            <td class="d-flex gap-1">
              <a class="btn btn-sm btn-outline-primary" href="homework.php?edit=<?= e((string)$r['id']) ?>">Edit</a>
              <form method="post" onsubmit="return confirm('Delete homework?')">
                <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e((string)$r['id']) ?>">
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
