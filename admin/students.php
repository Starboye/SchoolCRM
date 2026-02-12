<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_permission($db, 'can_manage_users');
$pageTitle = 'Manage Students';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = trim($_POST['id'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $standard = (int)($_POST['standard'] ?? 0);
        $section = trim($_POST['section'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['emailID'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($id !== '' && $name !== '') {
            $exists = mysqli_fetch_assoc(mysqli_query($db, "SELECT id FROM student_info WHERE id='" . mysqli_real_escape_string($db, $id) . "'"));
            if ($exists) {
                $stmt = mysqli_prepare($db, "UPDATE student_info SET name=?, standard=?, section=?, phone=?, emailID=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, 'sissss', $name, $standard, $section, $phone, $email, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                $stmt = mysqli_prepare($db, "UPDATE user_login SET name=? WHERE id=? AND access=0");
                mysqli_stmt_bind_param($stmt, 'ss', $name, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $msg = 'Student updated successfully.';
            } else {
                $stmt = mysqli_prepare($db, "INSERT INTO student_info (id, name, standard, section, phone, emailID) VALUES (?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, 'ssisss', $id, $name, $standard, $section, $phone, $email);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                if ($password === '') {
                    $password = 'pass123';
                }
                $stmt = mysqli_prepare($db, "INSERT INTO user_login (id, name, password, access) VALUES (?, ?, ?, 0)");
                mysqli_stmt_bind_param($stmt, 'sss', $id, $name, $password);
                @mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $msg = 'Student added successfully.';
            }
        }
    }

    if ($action === 'delete') {
        $id = trim($_POST['id'] ?? '');
        if (policy_requires_approval($db, 'user_delete')) {
            submit_approval($db, 'students', 'delete', 'student', $id, ['id'=>$id]);
            log_audit($db,'students','request_delete','student',$id,['id'=>$id],null);
            $msg = 'Delete queued for approval.';
        } else {
        $stmt = mysqli_prepare($db, "DELETE FROM student_info WHERE id=?");
        mysqli_stmt_bind_param($stmt, 's', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($db, "DELETE FROM user_login WHERE id=? AND access=0");
        mysqli_stmt_bind_param($stmt, 's', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $msg = 'Student deleted.';
        }
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $idEsc = mysqli_real_escape_string($db, $_GET['edit']);
    $edit = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM student_info WHERE id='$idEsc' LIMIT 1"));
}

$students = mysqli_query($db, "SELECT id, name, standard, section, phone, emailID FROM student_info ORDER BY standard, section, name");

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main id="main" class="main">
  <div class="pagetitle"><h1>Students</h1></div>

  <?php if ($msg): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>

  <div class="card"><div class="card-body">
    <h5 class="card-title"><?= $edit ? 'Edit Student' : 'Add Student' ?></h5>
    <form method="post" class="row g-2">
      <input type="hidden" name="action" value="save">
      <div class="col-md-2"><input name="id" class="form-control" placeholder="ID" required value="<?= e($edit['id'] ?? '') ?>" <?= $edit ? 'readonly' : '' ?>></div>
      <div class="col-md-3"><input name="name" class="form-control" placeholder="Name" required value="<?= e($edit['name'] ?? '') ?>"></div>
      <div class="col-md-2"><input type="number" name="standard" class="form-control" placeholder="Std" value="<?= e((string)($edit['standard'] ?? '')) ?>"></div>
      <div class="col-md-1"><input name="section" class="form-control" placeholder="Sec" value="<?= e($edit['section'] ?? '') ?>"></div>
      <div class="col-md-2"><input name="phone" class="form-control" placeholder="Phone" value="<?= e($edit['phone'] ?? '') ?>"></div>
      <div class="col-md-2"><input name="emailID" class="form-control" placeholder="Email" value="<?= e($edit['emailID'] ?? '') ?>"></div>
      <?php if (!$edit): ?><div class="col-md-2"><input name="password" class="form-control" placeholder="Login Password"></div><?php endif; ?>
      <div class="col-md-2"><button class="btn btn-primary w-100"><?= $edit ? 'Update' : 'Save' ?></button></div>
      <?php if ($edit): ?><div class="col-md-2"><a class="btn btn-secondary w-100" href="students.php">Cancel</a></div><?php endif; ?>
    </form>
  </div></div>

  <div class="card"><div class="card-body">
    <h5 class="card-title">All Students</h5>
    <div class="table-responsive">
      <table class="table table-sm table-striped">
        <thead><tr><th>ID</th><th>Name</th><th>Class</th><th>Phone</th><th>Email</th><th>Actions</th></tr></thead>
        <tbody>
          <?php while ($s = mysqli_fetch_assoc($students)): ?>
          <tr>
            <td><?= e($s['id']) ?></td>
            <td><?= e($s['name']) ?></td>
            <td><?= e((string)$s['standard'] . '-' . $s['section']) ?></td>
            <td><?= e($s['phone'] ?? '') ?></td>
            <td><?= e($s['emailID'] ?? '') ?></td>
            <td class="d-flex gap-1">
              <a class="btn btn-sm btn-outline-primary" href="students.php?edit=<?= urlencode($s['id']) ?>">Edit</a>
              <form method="post" onsubmit="return confirm('Delete student?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= e($s['id']) ?>">
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
