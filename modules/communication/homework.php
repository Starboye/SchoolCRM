<?php
session_start();

if (!isset($_SESSION["access"], $_SESSION["id"], $_SESSION["name"])) {
  header("Location: backoffice/login.php");
  exit();
}

$currentUserId   = $_SESSION["id"];
$currentUserName = $_SESSION["name"];
$access          = (int)$_SESSION["access"];   // 0 = student, 1 = teacher, 2 = admin (as used before)

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

// ----------------------------------------------------
// 1. Get student standard & section (for filtering)
// ----------------------------------------------------
$standard = null;
$section  = null;

$stuSql = "SELECT standard, section, name FROM student_info WHERE id = ?";
$stmt   = $conn->prepare($stuSql);
$stmt->bind_param("s", $currentUserId);
$stmt->execute();
$stuRes = $stmt->get_result();

if ($stuRes && $stuRes->num_rows > 0) {
  $stuRow   = $stuRes->fetch_assoc();
  $standard = $stuRow["standard"];
  $section  = $stuRow["section"];
} else {
  // If this is not a student (e.g. teacher/admin), you can adapt later.
  // For now, show a simple message.
  $standard = null;
  $section  = null;
}
$stmt->close();

// ----------------------------------------------------
// 2. Get selected date (default = today)
// ----------------------------------------------------
$today = date("Y-m-d");
$selectedDate = isset($_GET["date"]) && $_GET["date"] !== "" ? $_GET["date"] : $today;

// Basic validation: ensure format is YYYY-MM-DD
if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $selectedDate)) {
  $selectedDate = $today;
}

// Previous / Next dates (for navigation)
$prevDate = date("Y-m-d", strtotime($selectedDate . " -1 day"));
$nextDate = date("Y-m-d", strtotime($selectedDate . " +1 day"));

// ----------------------------------------------------
// 3. Fetch homework for that date + class/section
// ----------------------------------------------------
$homeworks = [];

if ($standard !== null && $section !== null) {
  $sql = "
    SELECT h.*, u.name AS teacher_name
    FROM homeworks h
    LEFT JOIN user_login u ON u.id = h.teacher_id
    WHERE h.standard = ?
      AND h.section  = ?
      AND h.date     = ?
    ORDER BY h.subject_name, h.id
  ";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("iss", $standard, $section, $selectedDate);
  $stmt->execute();
  $res = $stmt->get_result();

  if ($res) {
    while ($row = $res->fetch_assoc()) {
      $homeworks[] = $row;
    }
  }
  $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Homework - Asimos</title>
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
      <h1>Homework</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="studentDashboard.php">Home</a></li>
          <li class="breadcrumb-item active">Homework</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">

        <!-- Date selector + class info -->
        <div class="col-lg-12">
          <div class="card info-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">
                  Homework for
                  <span class="text-primary">
                    <?php echo e(date("d M Y", strtotime($selectedDate))); ?>
                  </span>
                </h5>
                <?php if ($standard !== null && $section !== null): ?>
                  <span class="badge bg-secondary">
                    Class <?php echo e($standard . $section); ?>
                  </span>
                <?php endif; ?>
              </div>

              <form method="get" class="row g-2 align-items-center">
                <div class="col-auto">
                  <label for="date" class="col-form-label">Select Date</label>
                </div>
                <div class="col-auto">
                  <input type="date" id="date" name="date"
                         class="form-control"
                         value="<?php echo e($selectedDate); ?>">
                </div>
                <div class="col-auto">
                  <button type="submit" class="btn btn-primary">
                    Go
                  </button>
                </div>
                <div class="col-auto ms-auto">
                  <a href="?date=<?php echo e($prevDate); ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-chevron-left"></i> Previous
                  </a>
                  <a href="?date=<?php echo e($nextDate); ?>" class="btn btn-outline-secondary btn-sm">
                    Next <i class="bi bi-chevron-right"></i>
                  </a>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Homework list -->
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Homework List</h5>

              <?php if ($standard === null || $section === null): ?>
                <p class="text-danger mb-0">
                  Could not detect your class and section. Please ensure your student profile is set correctly.
                </p>

              <?php elseif (empty($homeworks)): ?>
                <p class="text-muted mb-0">
                  No homework assigned for
                  <strong><?php echo e(date("d M Y", strtotime($selectedDate))); ?></strong>.
                </p>

              <?php else: ?>
                <div class="list-group">
                  <?php foreach ($homeworks as $hw): ?>
                    <div class="list-group-item mb-2" style="background:#f5f6fa; border:1px solid #e0e0e0; border-radius:10px; padding:15px 18px; transition:0.2s;" onmouseover="this.style.background='#eef0f4'; this.style.borderColor='#c8cbd1';" onmouseout="this.style.background='#f5f6fa'; this.style.borderColor='#e0e0e0';">

                    <div class="d-flex justify-content-between flex-wrap">
                        <div>
                          <h6 class="mb-1">
                            <span class="badge bg-primary me-2">
                              <?php echo e($hw["subject_name"]); ?>
                            </span>
                            <?php echo e($hw["title"]); ?>
                          </h6>
                        </div>
                        <div class="text-end">
                          <?php if (!empty($hw["teacher_name"])): ?>
                            <div class="small text-muted">
                              Given by: <?php echo e($hw["teacher_name"]); ?>
                            </div>
                          <?php endif; ?>
                          <div class="small text-muted">
                            Assigned on: <?php echo e(date("d M Y", strtotime($hw["date"]))); ?>
                          </div>
                        </div>
                      </div>
                      <p class="mt-2 mb-0">
                        <?php echo nl2br(e($hw["description"])); ?>
                      </p>
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

</body>
</html>