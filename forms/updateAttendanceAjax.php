<?php
session_start();
file_put_contents("debug.txt", $session . PHP_EOL, FILE_APPEND);

if($_SERVER['REQUEST_METHOD'] !== "POST"){
    exit;
}

$db = mysqli_connect("localhost","root","","asimos");
if(!$db){ die("DB failed"); }

$student_id  = $_POST['student_id'];
$status      = $_POST['status'];
$session     = $_POST['session'];
$date        = $_POST['date'];

$teacher_id  = $_SESSION['id'];
$markedBy    = $_SESSION['name'];

// check record exists
$check = mysqli_query($db,"
SELECT id FROM attendance
WHERE id='$student_id' AND date='$date'
LIMIT 1;
");

if(mysqli_num_rows($check)==0)
{
    // insert
    mysqli_query($db,"
    INSERT INTO attendance
    (id, name, date, $session, teacher_id, markedBy)
    VALUES
    (
        '$student_id',
        (SELECT name FROM student_info WHERE id='$student_id'),
        '$date',
        '$status',
        '$teacher_id',
        '$markedBy'
    );
    ");
}
else
{
    // update
    mysqli_query($db,"
    UPDATE attendance
    SET 
        $session = '$status',
        teacher_id = '$teacher_id',
        markedBy = '$markedBy'
    WHERE id='$student_id' AND date='$date';
    ");
}

echo "ok";
