<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
header('Location: ' . app_url('teacher/dashboard.php'), true, 302);
exit;
