<aside id="sidebar" class="sidebar">
  <ul class="sidebar-nav" id="sidebar-nav">
    <?php
    $current = basename((string)($_SERVER['SCRIPT_NAME'] ?? ''));
    $nav = function (string $href, string $icon, string $label, string $file) use ($current): void {
        $active = ($current === $file) ? ' active' : '';
        echo '<li class="nav-item">';
        echo '<a class="nav-link collapsed' . $active . '" href="' . e(app_url($href)) . '">';
        echo '<i class="bi ' . e($icon) . '"></i><span>' . e($label) . '</span></a></li>';
    };
    $nav('studentDashboard.php', 'bi-grid', 'Dashboard', 'studentDashboard.php');
    $nav('FeeDetails.php', 'bi-cash-coin', 'Fee Status', 'FeeDetails.php');
    $nav('TimeTable.php', 'bi-layout-text-window-reverse', 'Time Table', 'TimeTable.php');
    $nav('reportCard.php', 'bi-journal-text', 'Report Card', 'reportCard.php');
    $nav('homework.php', 'bi-card-list', 'Home Work', 'homework.php');
    $nav('announcements.php', 'bi-megaphone', 'Announcements', 'announcements.php');
    $nav('users-profile.php', 'bi-person-circle', 'My Profile', 'users-profile.php');
    $nav('changePassword.php', 'bi-gear', 'Change Password', 'changePassword.php');
    ?>
  </ul>
</aside>
