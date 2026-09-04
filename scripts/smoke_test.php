<?php
/**
 * CLI smoke test — run: php scripts/smoke_test.php
 */
declare(strict_types=1);

$root = dirname(__DIR__);
require $root . '/config/db.php';

$pass = 0;
$fail = 0;

function check(bool $ok, string $label): void {
    global $pass, $fail;
    if ($ok) {
        echo "[PASS] $label\n";
        $pass++;
    } else {
        echo "[FAIL] $label\n";
        $fail++;
    }
}

echo "=== SchoolCRM Smoke Test ===\n\n";

// DB
try {
    $pdo = db();
    check(true, 'Database connection');
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    check(in_array('user_login', $tables, true), 'user_login table exists');
    check(in_array('marks', $tables, true), 'marks table exists');
    check(in_array('marks_new', $tables, true), 'marks_new table exists');
} catch (Throwable $e) {
    check(false, 'Database connection: ' . $e->getMessage());
}

// Config
check(is_readable($root . '/.env'), '.env file exists');
check(defined('APP_BASE_PATH'), 'APP_BASE_PATH defined');
check(function_exists('app_url'), 'app_url() available');
check(function_exists('require_login'), 'require_login() available');
check(function_exists('app_password_verify'), 'app_password_verify() available');

// Key files
$files = [
    'index.php',
    'logout.php',
    'config/app.php',
    'config/db.php',
    'backoffice/login.php',
    'backoffice/changePassword.php',
    'admin/dashboard.php',
    'teacher/dashboard.php',
    'teacher/add_marks.php',
    'teacher/marks_manage.php',
    'teacher/attendance.php',
    'studentDashboard.php',
    'modules/common/account/changePassword.php',
    'modules/student/academic/reportCard.php',
    'modules/student/academic/reportCard_pdf.php',
    'partials/sidebar.php',
    'docs/USER_MANUAL.md',
];
foreach ($files as $f) {
    check(is_file($root . '/' . $f), "File exists: $f");
}

// No bad redirects in active PHP modules
$badPatterns = ['error-404.html', 'header("Location: backoffice/login.php")'];
$scanDirs = ['modules', 'teacher', 'admin', 'backoffice', 'forms'];
$badFound = false;
foreach ($scanDirs as $dir) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/' . $dir));
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $content = file_get_contents($file->getPathname());
        foreach ($badPatterns as $pat) {
            if (str_contains($content, $pat)) {
                $badFound = true;
                echo "[FAIL] Bad pattern '$pat' in " . $file->getPathname() . "\n";
                $fail++;
            }
        }
    }
}
if (!$badFound) {
    check(true, 'No error-404 or GET backoffice/login redirects in modules');
}

// student/ portal redirects
check(
    str_contains(file_get_contents($root . '/student/login.php'), "app_url('index.php')"),
    'student/login.php redirects to main login'
);

echo "\n=== Results: $pass passed, $fail failed ===\n";
exit($fail > 0 ? 1 : 0);
