<?php
// Backward-compatible entrypoint for legacy admin URL.
// Render dashboard directly to avoid redirect/path issues across environments.
require_once __DIR__ . '/../admin/dashboard.php';
// Backward-compatible entrypoint for legacy redirect targets.
header('Location: ../admin/dashboard.php');
exit;
