<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/portal_search.php';
app_session_start();

if (!isset($_SESSION['id'], $_SESSION['access'])) {
    header('Location: ' . app_url('index.php'));
    exit;
}

$query = trim((string)($_GET['q'] ?? $_POST['q'] ?? ''));
$access = (int)$_SESSION['access'];
$match = portal_search_resolve($query, $access);

if ($match !== null) {
    header('Location: ' . app_url($match['url']));
    exit;
}

$fallback = match ($access) {
    2 => app_url('admin/dashboard.php'),
    1 => app_url('teacher/dashboard.php'),
    default => app_url('studentDashboard.php'),
};

header('Location: ' . $fallback . '?search_error=1&q=' . rawurlencode($query));
exit;
