<?php
session_start();

/* ===============================
   DB CONNECTION
   =============================== */
$db = mysqli_connect("localhost", "root", "", "asimos");
if(!$db){ die("DB failed"); }

/* ===============================
   AUTH CHECK
   =============================== */
if(!isset($_SESSION['id'])){
    die("Unauthorized access");
}

$teacher_login_id = $_SESSION['id'];

/* ===============================
   FETCH TEACHER DETAILS
   =============================== */
$q = mysqli_query($db,"
    SELECT *
    FROM teacher_info
    WHERE teacher_id = '$teacher_login_id'
");

if(!$q || mysqli_num_rows($q) == 0){
    die("Teacher profile not found");
}

$t = mysqli_fetch_assoc($q);

/* ===============================
   MAP DATABASE COLUMNS
   =============================== */
$full_name     = trim(($t['first_name'] ?? '').' '.($t['last_name'] ?? ''));
$email         = $t['email'] ?? 'N/A';
$phone         = $t['phone'] ?? 'N/A';
$alt_phone     = $t['alt_phone'] ?? 'N/A';
$gender        = $t['gender'] ?? 'N/A';
$dob           = $t['date_of_birth'] ?? 'N/A';
$blood_group   = $t['blood_group'] ?? 'N/A';
$joining_date  = $t['date_of_joining'] ?? 'N/A';
$employment    = $t['employment_status'] ?? 'N/A';
$job_title     = $t['job_title'] ?? 'Teacher';
$employee_type = $t['employee_type'] ?? 'N/A';
$address       = $t['address'] ?? 'N/A';
$city          = $t['city'] ?? '';
$state         = $t['state'] ?? '';
$country       = $t['country'] ?? '';
$postal_code   = $t['postal_code'] ?? '';
?>

<?php include "includes/teacher_header.php"; ?>

<!-- ===== Sidebar ===== -->
<aside id="sidebar" class="sidebar">
  <div id="sidebar-container"></div>
  <script src="../teacher/includes/loadteacherSidebar.js"></script>
</aside>
<!-- ===== Sidebar ===== -->

<main id="main" class="main">

<div class="container-fluid mt-3">

<h3 class="mb-3">My Profile</h3>

<div class="card">
<div class="card-body">

    <h4 class="mb-0"><?= htmlspecialchars($full_name ?: 'N/A'); ?></h4>
    <small class="text-muted">
        <?= htmlspecialchars($job_title); ?> | <?= htmlspecialchars($employee_type); ?>
    </small>

    <hr>

    <div class="row">

        <!-- LEFT COLUMN -->
        <div class="col-md-6">
            <dl class="row">
                <dt class="col-sm-4">Email</dt>
                <dd class="col-sm-8"><?= htmlspecialchars($email); ?></dd>

                <dt class="col-sm-4">Phone</dt>
                <dd class="col-sm-8"><?= htmlspecialchars($phone); ?></dd>

                <dt class="col-sm-4">Alt Phone</dt>
                <dd class="col-sm-8"><?= htmlspecialchars($alt_phone); ?></dd>

                <dt class="col-sm-4">Gender</dt>
                <dd class="col-sm-8"><?= htmlspecialchars($gender); ?></dd>

                <dt class="col-sm-4">DOB</dt>
                <dd class="col-sm-8"><?= htmlspecialchars($dob); ?></dd>

                <dt class="col-sm-4">Blood Group</dt>
                <dd class="col-sm-8"><?= htmlspecialchars($blood_group); ?></dd>
            </dl>
        </div>

        <!-- RIGHT COLUMN -->
        <div class="col-md-6">
            <dl class="row">
                <dt class="col-sm-4">Teacher ID</dt>
                <dd class="col-sm-8"><?= htmlspecialchars($teacher_login_id); ?></dd>

                <dt class="col-sm-4">Joining Date</dt>
                <dd class="col-sm-8"><?= htmlspecialchars($joining_date); ?></dd>

                <dt class="col-sm-4">Status</dt>
                <dd class="col-sm-8"><?= htmlspecialchars($employment); ?></dd>

                <dt class="col-sm-4">Job Title</dt>
                <dd class="col-sm-8"><?= htmlspecialchars($job_title); ?></dd>

                <dt class="col-sm-4">Type</dt>
                <dd class="col-sm-8"><?= htmlspecialchars($employee_type); ?></dd>
            </dl>
        </div>

    </div>

    <hr>

    <h6 class="mb-2">Address</h6>
    <p class="mb-0">
        <?= htmlspecialchars($address); ?><br>
        <?= htmlspecialchars($city); ?>
        <?= $state ? ', '.htmlspecialchars($state) : ''; ?>
        <?= $country ? ', '.htmlspecialchars($country) : ''; ?><br>
        <?= $postal_code ? 'PIN: '.htmlspecialchars($postal_code) : ''; ?>
    </p>

</div>
</div>

</div>

</main>

<?php include "includes/teacher_footer.php"; ?>
