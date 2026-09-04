<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

header('Location: ' . app_url('logout.php'), true, 302);
exit;
