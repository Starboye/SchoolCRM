<?php
session_start();

// Make sure user is logged in
if (!isset($_SESSION["access"], $_SESSION["id"], $_SESSION["name"])) {
  header("Location: backoffice/login.php");
  exit();
}

$currentUserId   = $_SESSION["id"];
$currentUserName = $_SESSION["name"];

// DB connection
$servername  = "localhost";
$username_db = "root";
$password_db = "";
$dbname      = "asimos";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

function e($str) {
  return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// ---------------------------
// Password change handling
// ---------------------------
$passwordMessage     = "";
$passwordMessageType = "danger";

if (isset($_POST["change_password"])) {
  $current = $_POST["current_password"] ?? "";
  $new     = $_POST["new_password"] ?? "";
  $confirm = $_POST["confirm_password"] ?? "";

  if ($new === "" || $confirm === "" || $current === "") {
    $passwordMessage = "All fields are required.";
  } elseif ($new !== $confirm) {
    $passwordMessage = "New passwords do not match.";
  } elseif (strlen($new) < 6) {
    $passwordMessage = "New password should be at least 6 characters long.";
  } else {
    // Fetch existing password from DB
    $stmt = $conn->prepare("SELECT password FROM user_login WHERE id = ?");
    $stmt->bind_param("s", $currentUserId);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows === 1) {
      $row         = $res->fetch_assoc();
      $oldPassword = $row["password"];

      // NOTE: your DB currently stores plain text passwords,
      // so we compare directly. (No hashing.)
      if ($oldPassword !== $current) {
        $passwordMessage = "Current password is incorrect.";
      } else {
        // Update password
        $stmt2 = $conn->prepare("UPDATE user_login SET password = ? WHERE id = ?");
        $stmt2->bind_param("ss", $new, $currentUserId);

        if ($stmt2->execute()) {
          $passwordMessage     = "Password updated successfully!";
          $passwordMessageType = "success";
        } else {
          $passwordMessage = "Failed to update password. Please try again.";
        }

        $stmt2->close();
      }
    } else {
      $passwordMessage = "User not found.";
    }

    $stmt->close();
  }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Change Password - Asimos</title>
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Nunito:300,400,600,700|Poppins:300,400,600,700" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="studentDashboard.php" class="logo d-flex align-items-center">
        <img src="assets/img/logo.png" alt="">
        <span class="d-none d-lg-block">Asimos</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">
        <li class="nav-item dropdown pe-3">
          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <span class="d-none d-md-block dropdown-toggle ps-2"><?php echo e($currentUserName); ?></span>
          </a>
        </li>
      </ul>
    </nav>

  </header><!-- End Header -->

  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">
    <div id="sidebar-container"></div>
    <script src="assets/js/loadSidebar.js"></script>
  </aside>
  <!-- ======= Sidebar ======= -->

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

              <?php if (!empty($passwordMessage)): ?>
                <div class="alert alert-<?php echo e($passwordMessageType); ?>">
                  <?php echo e($passwordMessage); ?>
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

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/quill/quill.min.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

</body>
</html>
