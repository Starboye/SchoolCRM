<?php
session_start();

if (!isset($_SESSION["access"], $_SESSION["id"], $_SESSION["name"])) {
  header("Location: backoffice/login.php");
  exit();
}

$currentUserId   = $_SESSION["id"];
$currentUserName = $_SESSION["name"];
$access          = (int)$_SESSION["access"];

// Map access -> role
$role = "student";
if ($access === 2) {
  $role = "admin";
} elseif ($access === 1) {
  $role = "teacher";
}

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

// ----------------------
// Handle create announce
// ----------------------
$flashMessage = "";
$flashType    = "success";

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($role === "admin" || $role === "teacher")) {

  $targetType = $_POST["target_type"] ?? "";
  $message    = trim($_POST["message"] ?? "");
  $receiverId = trim($_POST["receiver_id"] ?? "");
  $today      = date("Y-m-d");
  $nowTime    = date("H:i");

  if ($message === "") {
    $flashMessage = "Announcement message cannot be empty.";
    $flashType    = "danger";
  } else {
    $allowedAdminTargets   = ["student", "teacher", "all_students", "all_teachers"];
    $allowedTeacherTargets = ["student"]; // teacher -> only specific student

    if ($role === "admin" && !in_array($targetType, $allowedAdminTargets, true)) {
      $flashMessage = "Invalid target type for admin.";
      $flashType    = "danger";
    } elseif ($role === "teacher" && !in_array($targetType, $allowedTeacherTargets, true)) {
      $flashMessage = "You are only allowed to send to individual students.";
      $flashType    = "danger";
    } else {

      $stmt = $conn->prepare("INSERT INTO notification (id, notification, sentBy, date, time, status)
                              VALUES (?, ?, ?, ?, ?, 0)");
      if (!$stmt) {
        die("Prepare failed: " . $conn->error);
      }

      $sentByLabel = ($role === "admin") ? "Admin" : "Teacher";
      $insertCount = 0;

      if ($targetType === "student") {
        if ($receiverId === "") {
          $flashMessage = "Please select a student.";
          $flashType    = "danger";
        } else {
          $stmt->bind_param("sssss", $receiverId, $message, $sentByLabel, $today, $nowTime);
          if ($stmt->execute()) {
            $insertCount = 1;
          }
        }

      } elseif ($targetType === "teacher" && $role === "admin") {
        if ($receiverId === "") {
          $flashMessage = "Please select a teacher.";
          $flashType    = "danger";
        } else {
          $stmt->bind_param("sssss", $receiverId, $message, $sentByLabel, $today, $nowTime);
          if ($stmt->execute()) {
            $insertCount = 1;
          }
        }

      } elseif ($targetType === "all_students" && $role === "admin") {
        $studentsRes = $conn->query("SELECT id FROM student_info");
        if ($studentsRes && $studentsRes->num_rows > 0) {
          while ($row = $studentsRes->fetch_assoc()) {
            $sid = $row["id"];
            $stmt->bind_param("sssss", $sid, $message, $sentByLabel, $today, $nowTime);
            if ($stmt->execute()) $insertCount++;
          }
        }

      } elseif ($targetType === "all_teachers" && $role === "admin") {
        $teachersRes = $conn->query("SELECT id, name FROM user_login WHERE access = 1");
        if ($teachersRes && $teachersRes->num_rows > 0) {
          while ($row = $teachersRes->fetch_assoc()) {
            $tid = $row["id"];
            $stmt->bind_param("sssss", $tid, $message, $sentByLabel, $today, $nowTime);
            if ($stmt->execute()) $insertCount++;
          }
        }
      }

      if ($insertCount > 0) {
        $flashMessage = "Announcement sent successfully to $insertCount recipient(s).";
        $flashType    = "success";
      } elseif ($flashMessage === "") {
        $flashMessage = "No recipients found or insert failed.";
        $flashType    = "danger";
      }

      $stmt->close();
    }
  }
}

// ----------------------
// Load students & teachers
// ----------------------
$students = [];
$teachers = [];

$studentsRes = $conn->query("SELECT id, name, standard, section FROM student_info ORDER BY standard, section, name");
if ($studentsRes) {
  while ($row = $studentsRes->fetch_assoc()) {
    $students[] = $row;
  }
}

// teachers from user_login (access = 1)
$teachersRes = $conn->query("SELECT id, name FROM user_login WHERE access = 1 ORDER BY name");
if ($teachersRes) {
  while ($row = $teachersRes->fetch_assoc()) {
    $teachers[] = ["id" => $row["id"], "name" => $row["name"]];
  }
}

// ----------------------
// Fetch notifications for current user
// ----------------------
$notifications = [];
if ($role === "admin") {
  $notifSql = "SELECT * FROM notification ORDER BY date DESC, time DESC";
  $notifRes = $conn->query($notifSql);
// } else {
//   $safeId   = $conn->real_escape_string($currentUserId);
//   $notifSql = "SELECT * FROM notification WHERE id = '$safeId' ORDER BY date DESC, time DESC";
//   $notifRes = $conn->query($notifSql);
// }
} else {

  // Get student's class & section
  $sid = $conn->real_escape_string($currentUserId);

  $res = $conn->query("
      SELECT standard, section 
      FROM student_info 
      WHERE id = '$sid' 
      LIMIT 1
  ");

  $row = $res->fetch_assoc();

  $std = $row['standard'];
  $sec = $row['section'];

  $classKey = "CLASS_{$std}_{$sec}";

  $notifSql = "
      SELECT * FROM notification
      WHERE id = '$sid'
         OR id = 'ALL'
         OR id = '$classKey'
      ORDER BY date DESC, time DESC
  ";

  $notifRes = $conn->query($notifSql);
}


if ($notifRes) {
  while ($row = $notifRes->fetch_assoc()) {
    $notifications[] = $row;
  }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Announcements - Asimos</title>
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
        <!-- <span class="d-none d-lg-block">Asimos</span> -->
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
      <h1>Announcements</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="studentDashboard.php">Home</a></li>
          <li class="breadcrumb-item active">Announcements</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">

        <div class="col-lg-12">
          <?php if ($flashMessage): ?>
            <div class="alert alert-<?php echo e($flashType); ?>">
              <?php echo e($flashMessage); ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Create announcement -->
        <?php if ($role === "admin" || $role === "teacher"): ?>
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Create Announcement</h5>

              <form method="post" class="row g-3">

                <div class="col-md-4">
                  <label for="target_type" class="form-label">Send To</label>
                  <select name="target_type" id="target_type" class="form-select" required>
                    <?php if ($role === "admin"): ?>
                      <option value="student">Specific Student</option>
                      <option value="teacher">Specific Teacher</option>
                      <option value="all_students">All Students</option>
                      <option value="all_teachers">All Teachers</option>
                    <?php elseif ($role === "teacher"): ?>
                      <option value="student">Specific Student</option>
                    <?php endif; ?>
                  </select>
                </div>

                <div class="col-md-4" id="receiver-wrapper">
                  <label for="receiver_id" class="form-label" id="receiver-label">Select Student</label>
                  <select name="receiver_id" id="receiver_id" class="form-select">
                    <option value="">-- Choose --</option>
                    <?php foreach ($students as $s): ?>
                      <option value="<?php echo e($s['id']); ?>">
                        <?php echo e($s['id']); ?> - <?php echo e($s['name']); ?> (<?php echo e($s['standard'] . $s['section']); ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-12">
                  <label for="message" class="form-label">Message</label>
                  <textarea name="message" id="message" class="form-control" rows="3" required></textarea>
                </div>

                <div class="col-12">
                  <button type="submit" class="btn btn-primary">Send Announcement</button>
                </div>

              </form>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- Announcements list -->
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Your Announcements</h5>

              <?php if (empty($notifications)): ?>
                <p class="text-muted mb-0">No announcements yet.</p>
              <?php else: ?>
                <div class="list-group">
                  <?php foreach ($notifications as $n): ?>
                    <div class="list-group-item">
                      <div class="d-flex justify-content-between">
                        <div>
                          <strong><?php echo e($n["sentBy"]); ?></strong>
                          <span class="text-muted small">
                            • <?php echo e($n["date"] . " " . $n["time"]); ?>
                          </span>
                        </div>
                      </div>
                      <p class="mb-0 mt-1"><?php echo nl2br(e($n["notification"])); ?></p>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

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

  <script>
  // Switch student/teacher dropdown based on target type
  document.addEventListener("DOMContentLoaded", function() {
    const targetType   = document.getElementById("target_type");
    const receiverWrap = document.getElementById("receiver-wrapper");
    const receiverId   = document.getElementById("receiver_id");
    const receiverLbl  = document.getElementById("receiver-label");

    if (!targetType || !receiverWrap || !receiverId || !receiverLbl) return;

    const studentsOptionsHTML = receiverId.innerHTML;
    const teachersOptionsHTML = `
      <option value="">-- Choose --</option>
      <?php foreach ($teachers as $t): ?>
        <option value="<?php echo e($t['id']); ?>">
          <?php echo e($t['id']); ?> - <?php echo e($t['name']); ?>
        </option>
      <?php endforeach; ?>
    `;

    function updateReceiverField() {
      const val = targetType.value;
      if (val === "student") {
        receiverWrap.style.display = "block";
        receiverLbl.textContent = "Select Student";
        receiverId.innerHTML = studentsOptionsHTML;
      } else if (val === "teacher") {
        receiverWrap.style.display = "block";
        receiverLbl.textContent = "Select Teacher";
        receiverId.innerHTML = teachersOptionsHTML;
      } else {
        receiverWrap.style.display = "none";
        receiverId.value = "";
      }
    }

    updateReceiverField();
    targetType.addEventListener("change", updateReceiverField);
  });
  </script>

</body>
</html>
