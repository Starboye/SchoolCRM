<?php
session_start();

// block direct access
if($_SERVER['REQUEST_METHOD'] !== "POST"){
    die("Invalid Access");
}

// DB
$db = mysqli_connect("localhost","root","","asimos");
if(!$db){ die("DB failed"); }

// Get values
$date       = $_POST['date'];
$session    = $_POST['session'];
$teacher_id = $_POST['teacher_id'];

// Loop students
foreach($_POST['attendance'] as $sid => $status)
{
    // check if row exists
    $check = mysqli_query($db,
    "SELECT id FROM attendance
     WHERE id='$sid' AND date='$date'
     LIMIT 1");

    if(mysqli_num_rows($check) == 0)
    {
        // insert new row with session value
        $insert = "
        INSERT INTO attendance (id, name, date, $session, teacher_id)
        VALUES (
            '$sid',
            (SELECT name FROM student_info WHERE id='$sid'),
            '$date',
            '$status',
            '$teacher_id'
        );
        ";

        mysqli_query($db,$insert);

    }
    else
    {
        // update session only
        $update = "
        UPDATE attendance
        SET $session = '$status'
        WHERE id='$sid' AND date='$date';
        ";

        mysqli_query($db,$update);
    }
}

header("Location: ../teacher/attendance.php?success=1");
exit;
