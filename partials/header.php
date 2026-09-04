  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <?php brand_logo(app_url('studentDashboard.php')); ?>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>

    <div class="search-bar">
      <form class="search-form d-flex align-items-center" method="get" action="<?= e(app_url('search.php')) ?>">
        <input type="text" name="q" placeholder="Search pages (e.g. timetable, homework, profile)" title="Search" value="<?= e((string)($_GET['q'] ?? '')) ?>">
        <button type="submit" title="Search"><i class="bi bi-search"></i></button>
      </form>
    </div>

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <li class="nav-item d-block d-lg-none">
          <a class="nav-link nav-icon search-bar-toggle" href="#"><i class="bi bi-search"></i></a>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-bell"></i>
            <?php if (!empty($notification) && is_array($notification)): ?>
              <span class="badge bg-primary badge-number"><?= count($notification) ?></span>
            <?php endif; ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
            <li class="dropdown-header">
              <?php $count = is_array($notification ?? null) ? count($notification) : 0; ?>
              You have <?= $count ?> notification<?= $count === 1 ? '' : 's' ?>
            </li>
            <li><hr class="dropdown-divider"></li>
            <?php if (!empty($notification) && is_array($notification)): ?>
              <?php foreach ($notification as $key => $value): ?>
                <li class="notification-item">
                  <i class="bi bi-info-circle text-primary"></i>
                  <div>
                    <h4><?= e((string)$key) ?></h4>
                    <p><?= e((string)$value) ?></p>
                  </div>
                </li>
                <li><hr class="dropdown-divider"></li>
              <?php endforeach; ?>
            <?php else: ?>
              <li class="notification-item text-muted px-3 py-2">No notifications</li>
            <?php endif; ?>
          </ul>
        </li>

        <li class="nav-item dropdown pe-3">
          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown" aria-expanded="false">
            <?= profile_image_html((string)($row['name'] ?? $username ?? 'Student'), $row['locOfProfilePic'] ?? null, 'rounded-circle', 36) ?>
            <span class="d-none d-md-block dropdown-toggle ps-2"><?= e((string)($username ?? '')) ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6><?= e((string)($username ?? '')) ?></h6>
              <span>Student</span><br>
              <?php if (!empty($row['standard']) || !empty($row['section'])): ?>
                <span><?= e((string)($row['standard'] ?? '')) ?> - <?= e((string)($row['section'] ?? '')) ?></span>
              <?php endif; ?>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item d-flex align-items-center" href="<?= e(app_url('users-profile.php')) ?>">
                <i class="bi bi-person"></i><span>My Profile</span>
              </a>
            </li>
            <li>
              <a class="dropdown-item d-flex align-items-center" href="<?= e(app_url('changePassword.php')) ?>">
                <i class="bi bi-gear"></i><span>Change Password</span>
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <?php profile_signout_item(); ?>
          </ul>
        </li>

      </ul>
    </nav>

  </header>
