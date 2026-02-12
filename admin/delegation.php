<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_permission($db,'can_manage_delegation');
header('Location: permissions.php');
exit;
