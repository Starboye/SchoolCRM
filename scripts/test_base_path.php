<?php
$_SERVER['DOCUMENT_ROOT'] = 'C:/xampp/htdocs';
$_SERVER['SCRIPT_NAME'] = '/SchoolCRM/index.php';
require __DIR__ . '/../config/app.php';
echo "BASE=[" . APP_BASE_PATH . "]\n";
echo "login=" . app_url('backoffice/login.php') . "\n";
echo "student=" . role_home(0) . "\n";
