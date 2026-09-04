<?php
declare(strict_types=1);

/**
 * Role-scoped portal search — maps keywords to in-app routes.
 */
function portal_search_catalog(int $access): array {
    $student = [
        ['keywords' => ['dashboard', 'home', 'main'], 'label' => 'Dashboard', 'url' => 'studentDashboard.php'],
        ['keywords' => ['fee', 'fees', 'payment', 'paid'], 'label' => 'Fee Status', 'url' => 'FeeDetails.php'],
        ['keywords' => ['timetable', 'time table', 'schedule', 'period', 'class'], 'label' => 'Time Table', 'url' => 'TimeTable.php'],
        ['keywords' => ['report', 'report card', 'marks', 'grade', 'result'], 'label' => 'Report Card', 'url' => 'reportCard.php'],
        ['keywords' => ['homework', 'home work', 'assignment'], 'label' => 'Home Work', 'url' => 'homework.php'],
        ['keywords' => ['announcement', 'notice', 'notification'], 'label' => 'Announcements', 'url' => 'announcements.php'],
        ['keywords' => ['profile', 'my profile', 'account'], 'label' => 'My Profile', 'url' => 'users-profile.php'],
        ['keywords' => ['password', 'change password', 'settings'], 'label' => 'Change Password', 'url' => 'changePassword.php'],
    ];

    $teacher = [
        ['keywords' => ['dashboard', 'home'], 'label' => 'Dashboard', 'url' => 'teacher/dashboard.php'],
        ['keywords' => ['attendance', 'present', 'absent'], 'label' => 'Attendance', 'url' => 'teacher/attendance.php'],
        ['keywords' => ['homework', 'assignment'], 'label' => 'Add Homework', 'url' => 'teacher/add_homework.php'],
        ['keywords' => ['marks', 'enter marks', 'add marks'], 'label' => 'Enter Marks', 'url' => 'teacher/add_marks.php'],
        ['keywords' => ['manage marks', 'view marks'], 'label' => 'Manage Marks', 'url' => 'teacher/marks_manage.php'],
        ['keywords' => ['announcement', 'notice'], 'label' => 'Announcements', 'url' => 'teacher/add_announcement.php'],
        ['keywords' => ['student', 'lookup', 'profile'], 'label' => 'Students', 'url' => 'teacher/view_studentData.php'],
        ['keywords' => ['class timetable', 'schedule', 'period'], 'label' => 'Class Timetable', 'url' => 'teacher/class_timetable.php'],
        ['keywords' => ['timetable', 'exam', 'schedule'], 'label' => 'Exam Timetable', 'url' => 'teacher/exam_timetable.php'],
        ['keywords' => ['my profile', 'profile'], 'label' => 'My Profile', 'url' => 'teacher/teacher_profile.php'],
    ];

    $admin = [
        ['keywords' => ['dashboard', 'home'], 'label' => 'Dashboard', 'url' => 'admin/dashboard.php'],
        ['keywords' => ['student', 'pupil'], 'label' => 'Students', 'url' => 'admin/students.php'],
        ['keywords' => ['teacher', 'staff'], 'label' => 'Teachers', 'url' => 'admin/teachers.php'],
        ['keywords' => ['attendance'], 'label' => 'Attendance', 'url' => 'admin/attendance.php'],
        ['keywords' => ['homework'], 'label' => 'Homework', 'url' => 'admin/homework.php'],
        ['keywords' => ['marks', 'grade'], 'label' => 'Marks', 'url' => 'admin/marks.php'],
        ['keywords' => ['notification', 'announcement'], 'label' => 'Notifications', 'url' => 'admin/notification_center.php'],
        ['keywords' => ['fee', 'fees', 'payment', 'structure'], 'label' => 'Fee Structure', 'url' => 'admin/fees.php'],
        ['keywords' => ['planner', 'timetable'], 'label' => 'Planner', 'url' => 'admin/planner.php'],
        ['keywords' => ['exam'], 'label' => 'Exams', 'url' => 'admin/exams.php'],
        ['keywords' => ['analytics', 'report'], 'label' => 'Analytics', 'url' => 'admin/analytics.php'],
        ['keywords' => ['security', 'login'], 'label' => 'Security', 'url' => 'admin/security.php'],
        ['keywords' => ['permission', 'rbac', 'role'], 'label' => 'RBAC', 'url' => 'admin/permissions.php'],
    ];

    return match ($access) {
        1 => $teacher,
        2 => $admin,
        default => $student,
    };
}

function portal_search_resolve(string $query, int $access): ?array {
    $query = strtolower(trim($query));
    if ($query === '') {
        return null;
    }

    foreach (portal_search_catalog($access) as $entry) {
        foreach ($entry['keywords'] as $keyword) {
            if ($query === $keyword || str_contains($query, $keyword) || str_contains($keyword, $query)) {
                return $entry;
            }
        }
    }

    return null;
}

function portal_search_suggestions(int $access): array {
    $out = [];
    foreach (portal_search_catalog($access) as $entry) {
        $out[] = ['label' => $entry['label'], 'url' => app_url($entry['url'])];
    }
    return $out;
}
