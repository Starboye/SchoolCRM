<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

header('Location: ' . app_url('reportCard.php'), true, 302);
exit;
