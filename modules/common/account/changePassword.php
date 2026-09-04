<?php
require_once __DIR__ . '/../../includes/portal.php';
require_login(0);

require_once __DIR__ . '/../../includes/student_context.php';
extract(student_portal_context());

$currentUserId   = $_SESSION['id'];
$currentUserName = $_SESSION['name'];

$conn = db_mysqli();

$passwordMessage     = '';
$passwordMessageType = 'danger';

if (isset($_POST['change_password'])) {
  $current = $_POST['current_password'] ?? '';
  $new     = $_POST['new_password'] ?? '';
  $confirm = $_POST['confirm_password'] ?? '';

  if ($new === '' || $confirm === '' || $current === '') {
    $passwordMessage = 'All fields are required.';
  } elseif ($new !== $confirm) {
    $passwordMessage = 'New passwords do not match.';
  } elseif (strlen($new) < 6) {
    $passwordMessage = 'New password should be at least 6 characters long.';
  } else {
    $stmt = $conn->prepare('SELECT password FROM user_login WHERE id = ?');
    $stmt->bind_param('s', $currentUserId);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows === 1) {
      $row         = $res->fetch_assoc();
      $oldPassword = (string)$row['password'];

      if (!app_password_verify($current, $oldPassword)) {
        $passwordMessage = 'Current password is incorrect.';
      } else {
        $hashed = app_password_hash($new);
        $stmt2 = $conn->prepare('UPDATE user_login SET password = ? WHERE id = ?');
        $stmt2->bind_param('ss', $hashed, $currentUserId);

        if ($stmt2->execute()) {
          $passwordMessage     = 'Password updated successfully!';
          $passwordMessageType = 'success';
        } else {
          $passwordMessage = 'Failed to update password. Please try again.';
        }

        $stmt2->close();
      }
    } else {
      $passwordMessage = 'User not found.';
    }

    $stmt->close();
  }
}
?>
<?php
$pageTitle = 'Change Password';
student_layout_start($pageTitle);
?>


  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Change Password</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="studentDashboard.php">Home</a></li>
          <li class="breadcrumb-item active">Change Password</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">

        <div class="col-lg-6">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Update your password</h5>

              <?php if ($passwordMessage !== ''): ?>
                <div class="alert alert-<?= e($passwordMessageType) ?>">
                  <?= e($passwordMessage) ?>
                </div>
              <?php endif; ?>

              <form method="POST">
                <div class="mb-3">
                  <label class="form-label">Current Password</label>
                  <input type="password" name="current_password" class="form-control" required>
                </div>

                <div class="mb-3">
                  <label class="form-label">New Password</label>
                  <input type="password" name="new_password" class="form-control" required minlength="6">
                </div>

                <div class="mb-3">
                  <label class="form-label">Confirm New Password</label>
                  <input type="password" name="confirm_password" class="form-control" required minlength="6">
                </div>

                <button type="submit" name="change_password" class="btn btn-primary">
                  Update Password
                </button>
              </form>

            </div>
          </div>
        </div>

      </div>
    </section>

  </main><!-- End #main -->
<?php student_layout_end(); ?>
