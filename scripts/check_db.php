<?php
require __DIR__ . '/../config/db.php';
try {
    $pdo = db();
    echo "PDO OK\n";
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo 'Tables: ' . count($tables) . "\n";
    $check = ['user_login','student_info','teacher_info','marks','marks_new','timetable_slots','exam_windows','audit_logs','permissions'];
    foreach ($check as $t) {
        echo $t . ': ' . (in_array($t, $tables, true) ? 'YES' : 'NO') . "\n";
    }
} catch (Throwable $e) {
    echo 'DB ERROR: ' . $e->getMessage() . "\n";
    exit(1);
}
