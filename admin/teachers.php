<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_permission($db, 'can_manage_users');
$pageTitle = 'Manage Teachers';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = trim($_POST['teacher_id'] ?? '');
        $first = trim($_POST['first_name'] ?? '');
        $last = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $job = trim($_POST['job_title'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $fullName = trim($first . ' ' . $last);

        if ($id !== '' && $first !== '') {
            $exists = mysqli_fetch_assoc(mysqli_query($db, "SELECT teacher_id FROM teacher_info WHERE teacher_id='" . mysqli_real_escape_string($db, $id) . "'"));
            if ($exists) {
                $stmt = mysqli_prepare($db, "UPDATE teacher_info SET first_name=?, last_name=?, phone=?, email=?, job_title=? WHERE teacher_id=?");
                mysqli_stmt_bind_param($stmt, 'ssssss', $first, $last, $phone, $email, $job, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                $stmt = mysqli_prepare($db, "UPDATE user_login SET name=? WHERE id=? AND access=1");
                mysqli_stmt_bind_param($stmt, 'ss', $fullName, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $msg = 'Teacher updated successfully.';
            } else {
                $stmt = mysqli_prepare($db, "INSERT INTO teacher_info (teacher_id, first_name, last_name, phone, email, job_title, login_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, 'sssssss', $id, $first, $last, $phone, $email, $job, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                if ($password === '') {
                    $password = 'pass123';
                }
                $stmt = mysqli_prepare($db, "INSERT INTO user_login (id, name, password, access) VALUES (?, ?, ?, 1)");
                mysqli_stmt_bind_param($stmt, 'sss', $id, $fullName, $password);
                @mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $msg = 'Teacher added successfully.';
            }
        }
    }

    if ($action === 'delete') {
        $id = trim($_POST['teacher_id'] ?? '');
        $stmt = mysqli_prepare($db, "DELETE FROM teacher_info WHERE teacher_id=?");
        mysqli_stmt_bind_param($stmt, 's', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($db, "DELETE FROM user_login WHERE id=? AND access=1");
        mysqli_stmt_bind_param($stmt, 's', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $msg = 'Teacher deleted.';
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $idEsc = mysqli_real_escape_string($db, $_GET['edit']);
    $edit = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM teacher_info WHERE teacher_id='$idEsc' LIMIT 1"));
}

$teachers = mysqli_query($db, "SELECT teacher_id, first_name, last_name, phone, email, job_title FROM teacher_info ORDER BY first_name, last_name");

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main id="main" class="main">
  <div class="pagetitle"><h1>Teachers</h1></div>

  <?php if ($msg): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>

  <div class="card"><div class="card-body">
    <h5 class="card-title"><?= $edit ? 'Edit Teacher' : 'Add Teacher' ?></h5>
    <form method="post" class="row g-2">
      <input type="hidden" name="action" value="save">
      <div class="col-md-2"><input name="teacher_id" class="form-control" placeholder="Teacher ID" required value="<?= e($edit['teacher_id'] ?? '') ?>" <?= $edit ? 'readonly' : '' ?>></div>
      <div class="col-md-2"><input name="first_name" class="form-control" placeholder="First Name" required value="<?= e($edit['first_name'] ?? '') ?>"></div>
      <div class="col-md-2"><input name="last_name" class="form-control" placeholder="Last Name" value="<?= e($edit['last_name'] ?? '') ?>"></div>
      <div class="col-md-2"><input name="phone" class="form-control" placeholder="Phone" value="<?= e($edit['phone'] ?? '') ?>"></div>
      <div class="col-md-2"><input name="email" class="form-control" placeholder="Email" value="<?= e($edit['email'] ?? '') ?>"></div>
      <div class="col-md-2"><input name="job_title" class="form-control" placeholder="Job Title" value="<?= e($edit['job_title'] ?? '') ?>"></div>
      <?php if (!$edit): ?><div class="col-md-2"><input name="password" class="form-control" placeholder="Login Password"></div><?php endif; ?>
      <div class="col-md-2"><button class="btn btn-primary w-100"><?= $edit ? 'Update' : 'Save' ?></button></div>
      <?php if ($edit): ?><div class="col-md-2"><a class="btn btn-secondary w-100" href="teachers.php">Cancel</a></div><?php endif; ?>
    </form>
  </div></div>

  <div class="card"><div class="card-body">
    <h5 class="card-title">All Teachers</h5>
    <div class="table-responsive">
      <table class="table table-sm table-striped">
        <thead><tr><th>ID</th><th>Name</th><th>Phone</th><th>Email</th><th>Role</th><th>Actions</th></tr></thead>
        <tbody>
          <?php while ($t = mysqli_fetch_assoc($teachers)): ?>
          <tr>
            <td><?= e($t['teacher_id']) ?></td>
            <td><?= e(trim(($t['first_name'] ?? '') . ' ' . ($t['last_name'] ?? ''))) ?></td>
            <td><?= e($t['phone'] ?? '') ?></td>
            <td><?= e($t['email'] ?? '') ?></td>
            <td><?= e($t['job_title'] ?? '') ?></td>
            <td class="d-flex gap-1">
              <a class="btn btn-sm btn-outline-primary" href="teachers.php?edit=<?= urlencode($t['teacher_id']) ?>">Edit</a>
              <form method="post" onsubmit="return confirm('Delete teacher?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="teacher_id" value="<?= e($t['teacher_id']) ?>">
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
