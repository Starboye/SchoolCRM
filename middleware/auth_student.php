<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

function render_header(string $title = 'Student'): void {
    if (file_exists(__DIR__ . '/../partials/header.php')) {
        require __DIR__ . '/../partials/header.php';
    } else {
        echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>' .
             htmlspecialchars($title) .
             '</title><link rel="stylesheet" href="' . e(app_url('assets/vendor/bootstrap/css/bootstrap.min.css')) . '"></head><body>';
    }
    if (file_exists(__DIR__ . '/../partials/sidebar.php')) {
        require __DIR__ . '/../partials/sidebar.php';
    }
}

function render_footer(): void {
    if (file_exists(__DIR__ . '/../partials/footer.php')) {
        require __DIR__ . '/../partials/footer.php';
    } else {
        echo '</body></html>';
    }
}

function require_student(): void {
    app_session_start();

    // Transition: main portal uses id; legacy student portal used user_id.
    if (!isset($_SESSION['id']) && isset($_SESSION['user_id'])) {
        $_SESSION['id'] = (string)$_SESSION['user_id'];
    }
    if (!isset($_SESSION['user_id']) && isset($_SESSION['id'])) {
        $_SESSION['user_id'] = (string)$_SESSION['id'];
    }

    if (!isset($_SESSION['id'], $_SESSION['access'])) {
        header('Location: ' . app_url('index.php'));
        exit;
    }

    if ((int)$_SESSION['access'] !== 0) {
        http_response_code(403);
        echo 'Forbidden: student only.';
        exit;
    }
}

function sanitize_date(string $d): string {
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) ? $d : date('Y-m-d');
}
