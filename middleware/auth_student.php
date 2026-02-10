<?php
declare(strict_types=1);
session_start();

function render_header(string $title = 'Student'): void {
  if (file_exists(__DIR__ . '/../partials/header.php')) {
    require __DIR__ . '/../partials/header.php';
  } else {
    echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>' .
         htmlspecialchars($title) .
         '</title><link rel="stylesheet" href="/assets/bootstrap.min.css"></head><body>';
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
  if (!isset($_SESSION['user_id'], $_SESSION['access'])) {
    header('Location: /student/login.php'); exit;
  }
  // access=0 => student (per your DB sample)
  if ((int)$_SESSION['access'] !== 0) {
    http_response_code(403);
    echo "Forbidden: student only.";
    exit;
  }
}

function sanitize_date(string $d): string {
  // Accepts YYYY-MM-DD only; fallback to today
  return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) ? $d : date('Y-m-d');
}
