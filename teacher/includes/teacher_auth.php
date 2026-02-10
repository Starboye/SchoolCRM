<?php
session_start();

if (!isset($_SESSION["access"], $_SESSION["id"], $_SESSION["name"])) {
    header("Location: ../backoffice/login.php");
    exit();
}

if ((int)$_SESSION["access"] !== 1) { // 1 = teacher
    die("Unauthorized Access");
}

$teacherId = $_SESSION["id"];
$teacherName = $_SESSION["name"];
?>
