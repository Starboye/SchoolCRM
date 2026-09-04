<?php require_once __DIR__ . '/teacher_auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <meta name="app-base" content="<?= e(APP_BASE_PATH) ?>">
  <title><?= e($pageTitle ?? 'Teacher Panel') ?> - <?= e(APP_NAME) ?></title>
  <?php brand_head_tags(); ?>
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Nunito:300,400,600,700|Poppins:300,400,600,700" rel="stylesheet">
  <?php portal_vendor_styles(); ?>
</head>
<body>
<?php portal_boot_screen(); ?>
<header id="header" class="header fixed-top d-flex align-items-center">
  <div class="d-flex align-items-center justify-content-between">
    <?php brand_logo(app_url('teacher/dashboard.php')); ?>
    <i class="bi bi-list toggle-sidebar-btn"></i>
  </div>
  <div class="search-bar">
    <form class="search-form d-flex align-items-center" method="get" action="<?= e(app_url('search.php')) ?>">
      <input type="text" name="q" placeholder="Search (attendance, marks, students…)" title="Search">
      <button type="submit" title="Search"><i class="bi bi-search"></i></button>
    </form>
  </div>
  <nav class="header-nav ms-auto">
    <ul class="d-flex align-items-center">
      <li class="nav-item d-block d-lg-none">
        <a class="nav-link nav-icon search-bar-toggle" href="#"><i class="bi bi-search"></i></a>
      </li>
      <li class="nav-item dropdown pe-3">
        <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown" aria-expanded="false">
          <?= profile_avatar_html($teacherName) ?>
          <span class="d-none d-md-block dropdown-toggle ps-2"><?= e($teacherName) ?></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
          <li class="dropdown-header">
            <h6><?= e($teacherName) ?></h6>
            <span>Teacher</span>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item d-flex align-items-center" href="teacher_profile.php">
              <i class="bi bi-person"></i><span>My Profile</span>
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
          <?php profile_signout_item(); ?>
        </ul>
      </li>
    </ul>
  </nav>
</header>
