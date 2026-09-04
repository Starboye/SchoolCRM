<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/portal.php';
require_once __DIR__ . '/../../includes/student_context.php';
require_login(0);

extract(student_portal_context());

$fullName = (string)($row['name'] ?? $username);
$standard = (string)($row['standard'] ?? '');
$section  = (string)($row['section'] ?? '');
$classLabel = trim($standard . ($section !== '' ? ' - ' . $section : ''));

$pageTitle = 'My Profile';
student_layout_start($pageTitle);
?>

<main id="main" class="main portal-profile">
  <div class="pagetitle">
    <h1>My Profile</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(app_url('studentDashboard.php')) ?>">Home</a></li>
        <li class="breadcrumb-item active">Profile</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="card">
      <div class="card-body profile-hero">
        <div class="d-flex align-items-center gap-3 mb-2">
          <?= profile_image_html($fullName, $row['locOfProfilePic'] ?? null, 'profile-avatar-lg', 96) ?>
          <div>
            <h2 class="mb-1"><?= e($fullName) ?></h2>
            <p class="subtitle">Student<?= $classLabel !== '' ? ' | Class ' . e($classLabel) : '' ?></p>
          </div>
        </div>
        <hr>
        <div class="row">
          <div class="col-md-6">
            <dl class="row mb-0">
              <dt class="col-sm-4">Student ID</dt>
              <dd class="col-sm-8"><?= e($id) ?></dd>
              <dt class="col-sm-4">Date of Birth</dt>
              <dd class="col-sm-8"><?= e((string)($row['dateOfBirth'] ?? 'N/A')) ?></dd>
              <dt class="col-sm-4">Gender</dt>
              <dd class="col-sm-8"><?= e((string)($row['gender'] ?? 'N/A')) ?></dd>
              <dt class="col-sm-4">Blood Group</dt>
              <dd class="col-sm-8"><?= e((string)($row['bloodGroup'] ?? 'N/A')) ?></dd>
              <dt class="col-sm-4">Phone</dt>
              <dd class="col-sm-8"><?= e((string)($row['phone'] ?? 'N/A')) ?></dd>
            </dl>
          </div>
          <div class="col-md-6">
            <dl class="row mb-0">
              <dt class="col-sm-4">Email</dt>
              <dd class="col-sm-8"><?= e((string)($row['emailID'] ?? 'N/A')) ?></dd>
              <dt class="col-sm-4">Class</dt>
              <dd class="col-sm-8"><?= e($classLabel !== '' ? $classLabel : 'N/A') ?></dd>
              <dt class="col-sm-4">Roll No</dt>
              <dd class="col-sm-8"><?= e((string)($row['rollNo'] ?? 'N/A')) ?></dd>
              <dt class="col-sm-4">Admission No</dt>
              <dd class="col-sm-8"><?= e((string)($row['admissionNo'] ?? 'N/A')) ?></dd>
              <dt class="col-sm-4">Address</dt>
              <dd class="col-sm-8"><?= e((string)($row['address'] ?? 'N/A')) ?></dd>
            </dl>
          </div>
        </div>
        <hr>
        <div class="d-flex flex-wrap gap-2">
          <a href="<?= e(app_url('changePassword.php')) ?>" class="btn btn-primary btn-sm"><i class="bi bi-gear me-1"></i> Change Password</a>
          <a href="<?= e(app_url('reportCard.php')) ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-journal-text me-1"></i> Report Card</a>
          <a href="<?= e(app_url('TimeTable.php')) ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-calendar3 me-1"></i> Time Table</a>
        </div>
      </div>
    </div>
  </section>
</main>

<?php student_layout_end(); ?>
