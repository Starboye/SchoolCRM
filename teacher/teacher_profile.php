<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/teacher_auth.php';

$pageTitle = 'My Profile';
$teacher_login_id = $teacherId;
$conn = db_mysqli();

$stmt = $conn->prepare('SELECT * FROM teacher_info WHERE teacher_id = ? LIMIT 1');
$stmt->bind_param('s', $teacher_login_id);
$stmt->execute();
$t = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$t) {
    $t = [
        'first_name' => $teacherName,
        'last_name' => '',
        'email' => 'N/A',
        'phone' => 'N/A',
        'alt_phone' => 'N/A',
        'gender' => 'N/A',
        'date_of_birth' => 'N/A',
        'blood_group' => 'N/A',
        'date_of_joining' => 'N/A',
        'employment_status' => 'N/A',
        'job_title' => 'Teacher',
        'employee_type' => 'N/A',
        'address' => 'N/A',
        'city' => '',
        'state' => '',
        'country' => '',
        'postal_code' => '',
    ];
}

$full_name     = trim(($t['first_name'] ?? '') . ' ' . ($t['last_name'] ?? ''));
$email         = (string)($t['email'] ?? 'N/A');
$phone         = (string)($t['phone'] ?? 'N/A');
$alt_phone     = (string)($t['alt_phone'] ?? 'N/A');
$gender        = (string)($t['gender'] ?? 'N/A');
$dob           = (string)($t['date_of_birth'] ?? 'N/A');
$blood_group   = (string)($t['blood_group'] ?? 'N/A');
$joining_date  = (string)($t['date_of_joining'] ?? 'N/A');
$employment    = (string)($t['employment_status'] ?? 'N/A');
$job_title     = (string)($t['job_title'] ?? 'Teacher');
$employee_type = (string)($t['employee_type'] ?? 'N/A');
$address       = (string)($t['address'] ?? 'N/A');
$city          = (string)($t['city'] ?? '');
$state         = (string)($t['state'] ?? '');
$country       = (string)($t['country'] ?? '');
$postal_code   = (string)($t['postal_code'] ?? '');

include __DIR__ . '/includes/teacher_header.php';
?>
<aside id="sidebar" class="sidebar">
  <div id="sidebar-container"></div>
  <script src="<?= e(app_url('teacher/includes/loadteacherSidebar.js')) ?>"></script>
</aside>

<main id="main" class="main portal-profile">
  <div class="pagetitle">
    <h1>My Profile</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
        <li class="breadcrumb-item active">Profile</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="card">
      <div class="card-body profile-hero">
        <div class="d-flex align-items-center gap-3 mb-2">
          <?= profile_avatar_html($full_name !== '' ? $full_name : $teacherName, 'profile-avatar profile-avatar-lg') ?>
          <div>
            <h2 class="mb-1"><?= e($full_name !== '' ? $full_name : $teacherName) ?></h2>
            <p class="subtitle"><?= e($job_title) ?> | <?= e($employee_type) ?></p>
          </div>
        </div>
        <hr>
        <div class="row">
          <div class="col-md-6">
            <dl class="row mb-0">
              <dt class="col-sm-4">Email</dt>
              <dd class="col-sm-8"><?= e($email) ?></dd>
              <dt class="col-sm-4">Phone</dt>
              <dd class="col-sm-8"><?= e($phone) ?></dd>
              <dt class="col-sm-4">Alt Phone</dt>
              <dd class="col-sm-8"><?= e($alt_phone) ?></dd>
              <dt class="col-sm-4">Gender</dt>
              <dd class="col-sm-8"><?= e($gender) ?></dd>
              <dt class="col-sm-4">DOB</dt>
              <dd class="col-sm-8"><?= e($dob) ?></dd>
              <dt class="col-sm-4">Blood Group</dt>
              <dd class="col-sm-8"><?= e($blood_group) ?></dd>
            </dl>
          </div>
          <div class="col-md-6">
            <dl class="row mb-0">
              <dt class="col-sm-4">Teacher ID</dt>
              <dd class="col-sm-8"><?= e($teacher_login_id) ?></dd>
              <dt class="col-sm-4">Joining Date</dt>
              <dd class="col-sm-8"><?= e($joining_date) ?></dd>
              <dt class="col-sm-4">Status</dt>
              <dd class="col-sm-8"><?= e($employment) ?></dd>
              <dt class="col-sm-4">Job Title</dt>
              <dd class="col-sm-8"><?= e($job_title) ?></dd>
              <dt class="col-sm-4">Type</dt>
              <dd class="col-sm-8"><?= e($employee_type) ?></dd>
            </dl>
          </div>
        </div>
        <hr>
        <h6 class="mb-2">Address</h6>
        <p class="mb-0 text-muted">
          <?= e($address) ?><br>
          <?= e(trim($city . ($state !== '' ? ', ' . $state : '') . ($country !== '' ? ', ' . $country : ''))) ?>
          <?= $postal_code !== '' ? '<br>PIN: ' . e($postal_code) : '' ?>
        </p>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/teacher_footer.php'; ?>
