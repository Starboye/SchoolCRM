<?php
/**
 * Truncate all data and seed South Indian demo records for live demo.
 *
 * Run: php scripts/seed_demo_data.php
 *
 * Default password for all demo users: Demo@2026
 */
declare(strict_types=1);

$root = dirname(__DIR__);
require $root . '/config/db.php';
require $root . '/includes/fee_helpers.php';
require $root . '/includes/timetable_helpers.php';

const DEMO_PASSWORD = 'Demo@2026';
const ACADEMIC_YEAR = '2026-27';
const CITY = 'Chennai';

$conn = db_mysqli();
$conn->set_charset('utf8mb4');

function out(string $msg): void {
    echo $msg . PHP_EOL;
}

function esc(mysqli $conn, string $v): string {
    return mysqli_real_escape_string($conn, $v);
}

function truncateAll(mysqli $conn): void {
    $sql = file_get_contents(dirname(__DIR__) . '/scripts/truncate_asimos.sql');
    if ($sql === false) {
        throw new RuntimeException('Could not read scripts/truncate_asimos.sql');
    }
    // Strip comments and run statement blocks
    if (!mysqli_multi_query($conn, $sql)) {
        throw new RuntimeException('Truncate failed: ' . mysqli_error($conn));
    }
    while (mysqli_more_results($conn) && mysqli_next_result($conn)) {
        // drain
    }
}

function insertUser(mysqli $conn, string $id, string $username, string $passwordHash, int $access): void {
    $stmt = $conn->prepare('INSERT INTO user_login (id, name, password, access) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('sssi', $id, $username, $passwordHash, $access);
    $stmt->execute();
    $stmt->close();
}

function insertStudent(
    mysqli $conn,
    string $id,
    string $name,
    int $standard,
    string $section,
    string $gender,
    string $dob,
    string $username
): void {
    $age = (int)date('Y') - (int)substr($dob, 0, 4);
    $addr = '12, Anna Nagar, ' . CITY;
    $blood = ['O+', 'A+', 'B+', 'AB+'][crc32($id) % 4];
    $phone = '98' . substr(str_pad((string)abs(crc32($id)), 8, '0'), 0, 8);
    $email = strtolower(str_replace(' ', '.', $username)) . '@asimos.edu.in';
    $father = explode(' ', $name)[0] . ' ' . ['Krishnan', 'Rajan', 'Murugan', 'Reddy', 'Nair'][crc32($id) % 5];
    $mother = ['Lakshmi', 'Priya', 'Kamala', 'Meena', 'Sujatha'][crc32($id . 'm') % 5] . ' ' . explode(' ', $name)[count(explode(' ', $name)) - 1];

    $stmt = $conn->prepare(
        'INSERT INTO student_info (id, name, age, standard, section, gender, dateOfBirth, address, bloodGroup, phone, emailID,
         fatherName, fatherOccupation, fatherPhone, motherName, motherOccupation, motherPhone, community, comments, locOfProfilePic)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $occ = 'Professional';
    $comm = 'General';
    $comments = '-';
    $pic = 'assets/img/default-student.svg';
    $stmt->bind_param(
        'ssiissssssssssssssss',
        $id,
        $name,
        $age,
        $standard,
        $section,
        $gender,
        $dob,
        $addr,
        $blood,
        $phone,
        $email,
        $father,
        $occ,
        $phone,
        $mother,
        $occ,
        $phone,
        $comm,
        $comments,
        $pic
    );
    $stmt->execute();
    $stmt->close();
}

function insertTeacher(mysqli $conn, array $t, string $passwordHash): void {
    insertUser($conn, $t['id'], $t['username'], $passwordHash, 1);
    $stmt = $conn->prepare(
        'INSERT INTO teacher_info (teacher_id, first_name, last_name, gender, date_of_birth, phone, email, address, city, state, country,
         date_of_joining, employment_status, job_title, employee_type, highest_qualification, specialization, login_id, basic_salary, hra, allowance_misc)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $addr = '45, Velachery Main Road, ' . CITY;
    $state = 'Tamil Nadu';
    $country = 'India';
    $joined = '2018-06-01';
    $status = 'Active';
    $title = 'Senior Teacher';
    $etype = 'Full-Time';
    $qual = 'M.A, B.Ed';
    $salary = 45000.00;
    $hra = 9000.00;
    $allow = 3500.00;
    $city = CITY;
    $stmt->bind_param(
        'ssssssssssssssssssddd',
        $t['id'],
        $t['first'],
        $t['last'],
        $t['gender'],
        $t['dob'],
        $t['phone'],
        $t['email'],
        $addr,
        $city,
        $state,
        $country,
        $joined,
        $status,
        $title,
        $etype,
        $qual,
        $t['subject'],
        $t['id'],
        $salary,
        $hra,
        $allow
    );
    $stmt->execute();
    $stmt->close();
}

// --- Demo roster ---
$passwordHash = app_password_hash(DEMO_PASSWORD);

$admin = ['id' => 'ADM001', 'username' => 'admin', 'name' => 'Lakshmi Sundaram'];
$adminId = $admin['id'];

$teachers = [
    ['id' => 'T001', 'username' => 'priya.ramachandran', 'first' => 'Priya', 'last' => 'Ramachandran', 'gender' => 'Female', 'dob' => '1988-04-12', 'phone' => '9840012345', 'email' => 'priya.r@asimos.edu.in', 'subject' => 'English'],
    ['id' => 'T002', 'username' => 'karthik.venkatesh', 'first' => 'Karthik', 'last' => 'Venkatesh', 'gender' => 'Male', 'dob' => '1986-09-03', 'phone' => '9840012346', 'email' => 'karthik.v@asimos.edu.in', 'subject' => 'Maths'],
    ['id' => 'T003', 'username' => 'ananya.iyer', 'first' => 'Ananya', 'last' => 'Iyer', 'gender' => 'Female', 'dob' => '1990-01-28', 'phone' => '9840012347', 'email' => 'ananya.i@asimos.edu.in', 'subject' => 'Science'],
    ['id' => 'T004', 'username' => 'farhan.mohammed', 'first' => 'Mohammed', 'last' => 'Farhan', 'gender' => 'Male', 'dob' => '1987-11-15', 'phone' => '9840012348', 'email' => 'farhan.m@asimos.edu.in', 'subject' => 'Social'],
    ['id' => 'T005', 'username' => 'divya.nair', 'first' => 'Divya', 'last' => 'Nair', 'gender' => 'Female', 'dob' => '1989-07-07', 'phone' => '9840012349', 'email' => 'divya.n@asimos.edu.in', 'subject' => 'Tamil'],
    ['id' => 'T006', 'username' => 'ramesh.babu', 'first' => 'Ramesh', 'last' => 'Babu', 'gender' => 'Male', 'dob' => '1985-03-22', 'phone' => '9840012350', 'email' => 'ramesh.b@asimos.edu.in', 'subject' => 'Computer Science'],
];

$studentRoster = [
    // 8-A
    ['id' => '88001', 'name' => 'Aditya Krishnan', 'std' => 8, 'sec' => 'A', 'gender' => 'Male', 'dob' => '2013-05-14', 'user' => 'aditya.krishnan'],
    ['id' => '88002', 'name' => 'Meera Subramanian', 'std' => 8, 'sec' => 'A', 'gender' => 'Female', 'dob' => '2013-08-02', 'user' => 'meera.subramanian'],
    ['id' => '88003', 'name' => 'Arjun Pillai', 'std' => 8, 'sec' => 'A', 'gender' => 'Male', 'dob' => '2013-02-19', 'user' => 'arjun.pillai'],
    ['id' => '88004', 'name' => 'Kavya Reddy', 'std' => 8, 'sec' => 'A', 'gender' => 'Female', 'dob' => '2013-11-30', 'user' => 'kavya.reddy'],
    ['id' => '88005', 'name' => 'Sanjay Menon', 'std' => 8, 'sec' => 'A', 'gender' => 'Male', 'dob' => '2013-01-08', 'user' => 'sanjay.menon'],
    ['id' => '88006', 'name' => 'Divya Shankar', 'std' => 8, 'sec' => 'A', 'gender' => 'Female', 'dob' => '2013-06-25', 'user' => 'divya.shankar'],
    ['id' => '88007', 'name' => 'Hariharan Gopal', 'std' => 8, 'sec' => 'A', 'gender' => 'Male', 'dob' => '2013-09-11', 'user' => 'hariharan.gopal'],
    ['id' => '88008', 'name' => 'Lakshmi Priya', 'std' => 8, 'sec' => 'A', 'gender' => 'Female', 'dob' => '2013-04-03', 'user' => 'lakshmi.priya'],
    ['id' => '88009', 'name' => 'Naveen Chandra', 'std' => 8, 'sec' => 'A', 'gender' => 'Male', 'dob' => '2013-12-17', 'user' => 'naveen.chandra'],
    ['id' => '88010', 'name' => 'Pooja Raman', 'std' => 8, 'sec' => 'A', 'gender' => 'Female', 'dob' => '2013-07-21', 'user' => 'pooja.raman'],
    ['id' => '88011', 'name' => 'Rohit Varma', 'std' => 8, 'sec' => 'A', 'gender' => 'Male', 'dob' => '2013-03-09', 'user' => 'rohit.varma'],
    ['id' => '88012', 'name' => 'Sneha Iyer', 'std' => 8, 'sec' => 'A', 'gender' => 'Female', 'dob' => '2013-10-05', 'user' => 'sneha.iyer'],
    // 8-B
    ['id' => '88013', 'name' => 'Anbu Selvam', 'std' => 8, 'sec' => 'B', 'gender' => 'Male', 'dob' => '2013-05-01', 'user' => 'anbu.selvam'],
    ['id' => '88014', 'name' => 'Bharath Kumar', 'std' => 8, 'sec' => 'B', 'gender' => 'Male', 'dob' => '2013-06-12', 'user' => 'bharath.kumar'],
    ['id' => '88015', 'name' => 'Charu Mohan', 'std' => 8, 'sec' => 'B', 'gender' => 'Female', 'dob' => '2013-08-20', 'user' => 'charu.mohan'],
    ['id' => '88016', 'name' => 'Deepika Raj', 'std' => 8, 'sec' => 'B', 'gender' => 'Female', 'dob' => '2013-02-14', 'user' => 'deepika.raj'],
    ['id' => '88017', 'name' => 'Gautham S', 'std' => 8, 'sec' => 'B', 'gender' => 'Male', 'dob' => '2013-11-08', 'user' => 'gautham.s'],
    ['id' => '88018', 'name' => 'Harini V', 'std' => 8, 'sec' => 'B', 'gender' => 'Female', 'dob' => '2013-01-27', 'user' => 'harini.v'],
    ['id' => '88019', 'name' => 'Karthik Raj', 'std' => 8, 'sec' => 'B', 'gender' => 'Male', 'dob' => '2013-09-16', 'user' => 'karthik.raj'],
    ['id' => '88020', 'name' => 'Malini Devi', 'std' => 8, 'sec' => 'B', 'gender' => 'Female', 'dob' => '2013-04-22', 'user' => 'malini.devi'],
    ['id' => '88021', 'name' => 'Pranav K', 'std' => 8, 'sec' => 'B', 'gender' => 'Male', 'dob' => '2013-07-03', 'user' => 'pranav.k'],
    ['id' => '88022', 'name' => 'Swathi N', 'std' => 8, 'sec' => 'B', 'gender' => 'Female', 'dob' => '2013-12-01', 'user' => 'swathi.n'],
    // 9-A
    ['id' => '89001', 'name' => 'Dinesh Kumar', 'std' => 9, 'sec' => 'A', 'gender' => 'Male', 'dob' => '2012-05-10', 'user' => 'dinesh.kumar'],
    ['id' => '89002', 'name' => 'Gayathri M', 'std' => 9, 'sec' => 'A', 'gender' => 'Female', 'dob' => '2012-08-18', 'user' => 'gayathri.m'],
    ['id' => '89003', 'name' => 'Immanuel Joseph', 'std' => 9, 'sec' => 'A', 'gender' => 'Male', 'dob' => '2012-03-25', 'user' => 'immanuel.joseph'],
    ['id' => '89004', 'name' => 'Janani S', 'std' => 9, 'sec' => 'A', 'gender' => 'Female', 'dob' => '2012-11-02', 'user' => 'janani.s'],
    ['id' => '89005', 'name' => 'Keerthana V', 'std' => 9, 'sec' => 'A', 'gender' => 'Female', 'dob' => '2012-01-15', 'user' => 'keerthana.v'],
    ['id' => '89006', 'name' => 'Manoj Babu', 'std' => 9, 'sec' => 'A', 'gender' => 'Male', 'dob' => '2012-06-28', 'user' => 'manoj.babu'],
    ['id' => '89007', 'name' => 'Nandini R', 'std' => 9, 'sec' => 'A', 'gender' => 'Female', 'dob' => '2012-09-09', 'user' => 'nandini.r'],
    ['id' => '89008', 'name' => 'Pradeep S', 'std' => 9, 'sec' => 'A', 'gender' => 'Male', 'dob' => '2012-04-17', 'user' => 'pradeep.s'],
    ['id' => '89009', 'name' => 'Revathi L', 'std' => 9, 'sec' => 'A', 'gender' => 'Female', 'dob' => '2012-12-22', 'user' => 'revathi.l'],
    ['id' => '89010', 'name' => 'Vishnu Prasad', 'std' => 9, 'sec' => 'A', 'gender' => 'Male', 'dob' => '2012-07-06', 'user' => 'vishnu.prasad'],
];

$subjects = ['English', 'Tamil', 'Maths', 'Science', 'Social', 'Computer Science'];
$classSections = [[8, 'A'], [8, 'B'], [9, 'A']];

out('=== Asimos SchoolCRM — Demo seed ===');
out('Truncating existing data...');
truncateAll($conn);
out('Seeding system configuration...');

// Permissions
$permissions = [
    ['can_manage_users', 'Create/update/delete users'],
    ['can_edit_marks', 'Edit marks records'],
    ['can_delete_attendance', 'Delete or override attendance records'],
    ['can_manage_notifications', 'Broadcast and schedule notifications'],
    ['can_manage_planner', 'Manage timetable and workload planner'],
    ['can_manage_exams', 'Manage exam windows and marks lifecycle'],
    ['can_manage_fees', 'Manage class fee structures and student payment status'],
    ['can_view_analytics', 'View analytics dashboards'],
    ['can_manage_security', 'Manage security policies and lockouts'],
    ['can_manage_data_quality', 'Run and resolve data quality checks'],
    ['can_manage_delegation', 'Assign sub-admin roles'],
];
$pStmt = $conn->prepare('INSERT INTO permissions (permission_key, description) VALUES (?, ?)');
foreach ($permissions as [$key, $desc]) {
    $pStmt->bind_param('ss', $key, $desc);
    $pStmt->execute();
}
$pStmt->close();

// Roles
$roles = [
    [1, 'Super Admin', 'global'],
    [2, 'Academic Admin', 'academic'],
    [3, 'HR Admin', 'hr'],
    [4, 'Exam Admin', 'exam'],
    [5, 'Ops Admin', 'ops'],
];
$rStmt = $conn->prepare('INSERT INTO roles (role_id, role_name, role_scope) VALUES (?, ?, ?)');
foreach ($roles as [$rid, $rname, $scope]) {
    $rStmt->bind_param('iss', $rid, $rname, $scope);
    $rStmt->execute();
}
$rStmt->close();

$conn->query('INSERT INTO role_permissions (role_id, permission_key) SELECT 1, permission_key FROM permissions');

$policies = [
    ['marks_edit', 1], ['attendance_override', 1], ['user_delete', 1], ['homework_delete', 0], ['timetable_submit', 1],
];
$ap = $conn->prepare('INSERT INTO approval_policies (policy_key, require_approval) VALUES (?, ?)');
foreach ($policies as [$k, $v]) {
    $ap->bind_param('si', $k, $v);
    $ap->execute();
}
$ap->close();

$sec = [
    ['max_failed_attempts', '5'], ['session_timeout_minutes', '60'], ['password_min_length', '8'],
];
$sp = $conn->prepare('INSERT INTO security_policies (policy_key, policy_value) VALUES (?, ?)');
foreach ($sec as [$k, $v]) {
    $sp->bind_param('ss', $k, $v);
    $sp->execute();
}
$sp->close();

out('Seeding users...');
insertUser($conn, $admin['id'], $admin['username'], $passwordHash, 2);
$conn->query("INSERT INTO user_roles (user_id, role_id, assigned_by) VALUES ('{$admin['id']}', 1, '{$admin['id']}')");

foreach ($teachers as $t) {
    insertTeacher($conn, $t, $passwordHash);
}

foreach ($studentRoster as $s) {
    insertUser($conn, $s['id'], $s['user'], $passwordHash, 0);
    insertStudent($conn, $s['id'], $s['name'], $s['std'], $s['sec'], $s['gender'], $s['dob'], $s['user']);
}

out('Seeding class catalog & subjects...');
$csId = 1;
$csMap = [];
foreach ($classSections as [$std, $sec]) {
    $conn->query("INSERT INTO class_sections (id, standard, section) VALUES ($csId, '$std', '$sec')");
    $csMap["$std-$sec"] = $csId++;
}

$subId = 1;
$subMap = [];
foreach ($subjects as $sub) {
    $conn->query("INSERT INTO subjects (id, subject_name) VALUES ($subId, '" . esc($conn, $sub) . "')");
    $subMap[$sub] = $subId++;
    foreach ($csMap as $key => $csid) {
        $conn->query("INSERT INTO class_subjects (class_section_id, subject_id) VALUES ($csid, {$subMap[$sub]})");
    }
}

out('Seeding teacher allocations...');
$allocId = 1;
$allocStmt = $conn->prepare('INSERT INTO teacher_subject_allocation (id, teacher_id, subject_name, standard, section) VALUES (?, ?, ?, ?, ?)');
$teacherBySubject = [];
foreach ($teachers as $t) {
    $teacherBySubject[$t['subject']] = $t['id'];
}
foreach ($classSections as [$std, $sec]) {
    foreach ($subjects as $sub) {
        $tid = $teacherBySubject[$sub];
        $allocStmt->bind_param('issis', $allocId, $tid, $sub, $std, $sec);
        $allocStmt->execute();
        $allocId++;
    }
}
$allocStmt->close();

// Class teachers
$ctAssignments = [
    [8, 'A', 'T001'], [8, 'B', 'T002'], [9, 'A', 'T006'],
];
$ctStmt = $conn->prepare(
    'INSERT INTO class_teacher_assignments (standard, section, academic_year, class_teacher_id, assigned_by) VALUES (?, ?, ?, ?, ?)'
);
$year = ACADEMIC_YEAR;
foreach ($ctAssignments as [$std, $sec, $tid]) {
    $ctStmt->bind_param('issss', $std, $sec, $year, $tid, $adminId);
    $ctStmt->execute();
}
$ctStmt->close();

out('Seeding timetables (approved)...');
$timetableTemplate = [
    1 => ['English', 'Maths', 'Science', 'Tamil', 'Social', 'Computer Science'],
    2 => ['Maths', 'Science', 'English', 'Social', 'Tamil', 'Computer Science'],
    3 => ['Science', 'English', 'Maths', 'Tamil', 'Computer Science', 'Social'],
    4 => ['Tamil', 'Social', 'Science', 'Maths', 'English', 'Computer Science'],
    5 => ['Social', 'Tamil', 'English', 'Science', 'Maths', 'Computer Science'],
];
$ttStmt = $conn->prepare(
    'INSERT INTO timetable_slots (teacher_id, standard, section, subject_name, day_of_week, period_no, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)'
);
$ctStmt2 = $conn->prepare(
    'INSERT INTO class_timetables (standard, section, academic_year, status, submitted_by, submitted_at, reviewed_by, reviewed_at) VALUES (?, ?, ?, "approved", ?, NOW(), ?, NOW())'
);
$year = ACADEMIC_YEAR;
foreach ($classSections as [$std, $sec]) {
    $ctId = ($std === 8 && $sec === 'A') ? 'T001' : (($std === 8 && $sec === 'B') ? 'T002' : 'T006');
    $ctStmt2->bind_param('issss', $std, $sec, $year, $ctId, $adminId);
    $ctStmt2->execute();
    for ($day = 1; $day <= 5; $day++) {
        for ($period = 1; $period <= 6; $period++) {
            $sub = $timetableTemplate[$day][$period - 1];
            $tid = $teacherBySubject[$sub];
            $ttStmt->bind_param('sissiis', $tid, $std, $sec, $sub, $day, $period, $adminId);
            $ttStmt->execute();
        }
    }
}
$ttStmt->close();
$ctStmt2->close();

out('Seeding fee structures...');
$feeTerms = ['Term 1', 'Term 2', 'Term 3'];
$feeBase = [
    8 => ['term' => 3500, 'special' => 500, 'tuition' => 12000, 'lab' => 800],
    9 => ['term' => 4000, 'special' => 600, 'tuition' => 13500, 'lab' => 1000],
];
$fsStmt = $conn->prepare(
    'INSERT INTO fee_structures (standard, section, term_label, term_fee, special_fee, tuition_fee, lab_fee, total_fee, academic_year, updated_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$year = ACADEMIC_YEAR;
foreach ($classSections as [$std, $sec]) {
    $fb = $feeBase[$std];
    foreach ($feeTerms as $term) {
        $total = $fb['term'] + $fb['special'] + $fb['tuition'] + $fb['lab'];
        $fsStmt->bind_param(
            'issddddsss',
            $std,
            $sec,
            $term,
            $fb['term'],
            $fb['special'],
            $fb['tuition'],
            $fb['lab'],
            $total,
            $year,
            $adminId
        );
        $fsStmt->execute();
    }
}
$fsStmt->close();

$sfsPaidStmt = $conn->prepare(
    'INSERT INTO student_fee_status (student_id, fee_structure_id, status, amount_paid, paid_on, updated_by) VALUES (?, ?, ?, ?, ?, ?)'
);
$sfsUnpaidStmt = $conn->prepare(
    'INSERT INTO student_fee_status (student_id, fee_structure_id, status, amount_paid, paid_on, updated_by) VALUES (?, ?, ?, ?, NULL, ?)'
);
$statusCycle = ['paid', 'paid', 'unpaid', 'partial', 'paid'];
$fsRes = $conn->query('SELECT id, standard, section, total_fee FROM fee_structures ORDER BY id');
while ($fs = $fsRes->fetch_assoc()) {
    foreach ($studentRoster as $i => $s) {
        if ((int)$s['std'] !== (int)$fs['standard'] || $s['sec'] !== $fs['section']) {
            continue;
        }
        $st = $statusCycle[$i % count($statusCycle)];
        $paid = $st === 'paid' ? (float)$fs['total_fee'] : ($st === 'partial' ? (float)$fs['total_fee'] * 0.5 : 0);
        if ($st === 'unpaid') {
            $sfsUnpaidStmt->bind_param('sisds', $s['id'], $fs['id'], $st, $paid, $adminId);
            $sfsUnpaidStmt->execute();
        } else {
            $paidOn = '2026-06-15';
            $sfsPaidStmt->bind_param('sisdss', $s['id'], $fs['id'], $st, $paid, $paidOn, $adminId);
            $sfsPaidStmt->execute();
        }
    }
}
$sfsPaidStmt->close();
$sfsUnpaidStmt->close();

out('Seeding marks & report cards...');
$markStmt = $conn->prepare(
    'INSERT INTO marks (id, subjectName, testName, date, marksObtained, totalMarks) VALUES (?, ?, ?, ?, ?, ?)'
);
$mnStmt = $conn->prepare(
    'INSERT INTO marks_new (id, subjectName, testName, date, grandTotal, totalMarks, english, tamil, maths, science, social) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$terms = ['Term 1', 'Term 2', 'Term 3'];
$subjectsMarks = ['English', 'Tamil', 'Maths', 'Science', 'Social'];
foreach ($studentRoster as $s) {
    foreach ($terms as $ti => $term) {
        $scores = [];
        $total = 0;
        foreach ($subjectsMarks as $sub) {
            $obt = 55 + (($i = crc32($s['id'] . $term . $sub)) % 46);
            $scores[$sub] = $obt;
            $total += $obt;
            $date = '2026-0' . ($ti + 4) . '-15';
            $obtS = (string)$obt;
            $totS = '100';
            $markStmt->bind_param('ssssss', $s['id'], $sub, $term, $date, $obtS, $totS);
            $markStmt->execute();
        }
        $grand = (string)$total;
        $totMarks = '500';
        $date = '2026-0' . ($ti + 4) . '-15';
        $dummy = '-';
        $mnStmt->bind_param(
            'ssssssiiiii',
            $s['id'],
            $dummy,
            $term,
            $date,
            $grand,
            $totMarks,
            $scores['English'],
            $scores['Tamil'],
            $scores['Maths'],
            $scores['Science'],
            $scores['Social']
        );
        $mnStmt->execute();
    }
}
$markStmt->close();
$mnStmt->close();

out('Seeding homework & notifications...');
$hwStmt = $conn->prepare(
    'INSERT INTO homeworks (subject_name, teacher_id, standard, section, date, title, description, target_type) VALUES (?, ?, ?, ?, ?, ?, ?, "class")'
);
$homeworks = [
    ['English', 'T001', 8, 'A', 'Read Chapter 4 and write a summary (150 words).'],
    ['Maths', 'T002', 8, 'A', 'Complete exercises 5.1 to 5.3 from textbook.'],
    ['Science', 'T003', 9, 'A', 'Prepare a chart on the water cycle.'],
];
$today = date('Y-m-d');
foreach ($homeworks as [$sub, $tid, $std, $sec, $desc]) {
    $title = $sub . ' — Assignment';
    $hwStmt->bind_param('ssissss', $sub, $tid, $std, $sec, $today, $title, $desc);
    $hwStmt->execute();
}
$hwStmt->close();

$notifications = [
    ['ALL', null, 'Welcome to the new academic year 2026-27. Orientation on Monday at 9 AM.', 'Admin Office'],
    ['CLASS_8_A', null, 'Class 8-A PTA meeting scheduled for Friday 4 PM.', 'Priya Ramachandran'],
    ['89001', null, 'Your science project submission is due next week.', 'Ananya Iyer'],
];
$nStmt = $conn->prepare('INSERT INTO notification (id, notification, sentBy, date, time, status) VALUES (?, ?, ?, ?, ?, 0)');
$ndate = date('d-m-Y');
$ntime = date('H:i');
foreach ($notifications as [$target, , $msg, $by]) {
    $nStmt->bind_param('sssss', $target, $msg, $by, $ndate, $ntime);
    $nStmt->execute();
}
$nStmt->close();

out('Seeding attendance (last 10 school days)...');
$attStmt = $conn->prepare(
    'INSERT INTO attendance (id, date, name, morning, afternoon, evening, teacher_id, markedBy) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
);
for ($d = 1; $d <= 10; $d++) {
    $date = date('Y-m-d', strtotime("-$d weekdays"));
    foreach ($studentRoster as $s) {
        $present = (crc32($s['id'] . $date) % 10) > 1 ? 1 : 0;
        $tid = 'T001';
        $attStmt->bind_param('sssiiiss', $s['id'], $date, $s['name'], $present, $present, $present, $tid, $tid);
        $attStmt->execute();
    }
}
$attStmt->close();

out('Seeding assessments (class tests)...');
$assessStmt = $conn->prepare(
    'INSERT INTO assessments (id, date, test, subjectName, marks, Result) VALUES (?, ?, ?, ?, ?, ?)'
);
$unitTests = ['Unit 1', 'Unit 2', 'Mid-Term'];
$assessSubjects = ['English', 'Tamil', 'Maths', 'Science', 'Social'];
foreach ($studentRoster as $s) {
    $studentIdInt = (int)$s['id'];
    foreach ($assessSubjects as $sub) {
        foreach ($unitTests as $ut) {
            $obt = 28 + (crc32($s['id'] . $sub . $ut) % 23);
            $marksStr = $obt . '/50';
            $result = $obt >= 25 ? 'PASS' : 'FAIL';
            $daysAgo = crc32($s['id'] . $ut . $sub) % 90;
            $date = date('d-m-Y', strtotime("-{$daysAgo} days"));
            $assessStmt->bind_param('isssss', $studentIdInt, $date, $ut, $sub, $marksStr, $result);
            $assessStmt->execute();
        }
    }
}
$assessStmt->close();

out('Seeding exams, exam windows & marks_master...');
$ewStmt = $conn->prepare(
    'INSERT INTO exam_windows (exam_name, starts_on, ends_on, marks_entry_locked, marks_published, created_by) VALUES (?, ?, ?, ?, ?, ?)'
);
$examWindows = [
    ['Term 1 Exams', '2026-06-01', '2026-06-20', 0, 1],
    ['Term 2 Exams', '2026-09-01', '2026-09-25', 0, 0],
    ['Annual Exam', '2027-02-01', '2027-02-28', 1, 0],
];
foreach ($examWindows as [$ename, $start, $end, $locked, $published]) {
    $ewStmt->bind_param('sssiis', $ename, $start, $end, $locked, $published, $adminId);
    $ewStmt->execute();
}
$ewStmt->close();

$examStmt = $conn->prepare(
    'INSERT INTO exams (exam_name, standard, section, exam_date, created_by) VALUES (?, ?, ?, ?, ?)'
);
$mmStmt = $conn->prepare('INSERT INTO marks_master (exam_id, student_id) VALUES (?, ?)');
$mdStmt = $conn->prepare(
    'INSERT INTO marks_details (mark_id, subject_id, marks_obtained, total_marks) VALUES (?, ?, ?, ?)'
);
foreach ($classSections as [$std, $sec]) {
    $examName = "Term 1 — Class {$std}-{$sec}";
    $examDate = '2026-06-18';
    $stdStr = (string)$std;
    $examStmt->bind_param('sssss', $examName, $stdStr, $sec, $examDate, $adminId);
    $examStmt->execute();
    $currentExamId = (int)$conn->insert_id;
    foreach ($studentRoster as $s) {
        if ((int)$s['std'] !== $std || $s['sec'] !== $sec) {
            continue;
        }
        $mmStmt->bind_param('is', $currentExamId, $s['id']);
        $mmStmt->execute();
        $markId = (int)$conn->insert_id;
        foreach ($subMap as $subName => $subjectId) {
            if (!in_array($subName, $assessSubjects, true)) {
                continue;
            }
            $obt = 60 + (crc32($s['id'] . $subName . 'exam') % 41);
            $totalMarks = 100;
            $mdStmt->bind_param('iiii', $markId, $subjectId, $obt, $totalMarks);
            $mdStmt->execute();
        }
    }
}
$examStmt->close();
$mmStmt->close();
$mdStmt->close();

out('Seeding extra homework (all classes)...');
$hwStmt2 = $conn->prepare(
    'INSERT INTO homeworks (subject_name, teacher_id, standard, section, date, title, description, target_type) VALUES (?, ?, ?, ?, ?, ?, ?, "class")'
);
$hwTemplates = [
    'English' => 'Read the assigned lesson and write comprehension answers.',
    'Tamil' => 'பாடம் 3 படித்து சுருக்கம் எழுதுக.',
    'Maths' => 'Solve problems from chapter exercises (odd numbers).',
    'Science' => 'Draw and label the diagram discussed in class.',
    'Social' => 'Prepare short notes on the topic covered today.',
    'Computer Science' => 'Practice HTML basics — create a simple profile page.',
];
foreach ($classSections as [$std, $sec]) {
    foreach ($subjects as $sub) {
        $tid = $teacherBySubject[$sub];
        for ($d = 0; $d < 5; $d++) {
            $hwDate = date('Y-m-d', strtotime("-{$d} weekdays"));
            $title = $sub . ' — ' . date('d M', strtotime($hwDate));
            $desc = $hwTemplates[$sub] ?? 'Complete the assigned work.';
            $hwStmt2->bind_param('ssissss', $sub, $tid, $std, $sec, $hwDate, $title, $desc);
            $hwStmt2->execute();
        }
    }
}
$hwStmt2->close();

out('Seeding notifications & templates...');
$conn->query(
    "INSERT INTO notification_templates (name, body, created_by) VALUES
    ('PTA Reminder', 'Dear parent, PTA meeting for {class} is on Friday at 4 PM.', '{$admin['id']}'),
    ('Fee Reminder', 'School fee for {term} is due by the 15th. Kindly pay at the office.', '{$admin['id']}'),
    ('Holiday Notice', 'School will remain closed on account of the festival. Classes resume Monday.', '{$admin['id']}')"
);
$snStmt = $conn->prepare(
    'INSERT INTO scheduled_notifications (target_type, target_value, message, scheduled_at, status, created_by) VALUES (?, ?, ?, ?, ?, ?)'
);
$snStmt->bind_param('ssssss', $tType, $tVal, $msg, $sched, $status, $by);
$tType = 'CLASS';
$tVal = '8_A';
$msg = 'Reminder: Science exhibition submissions due next week.';
$sched = date('Y-m-d H:i:s', strtotime('+3 days'));
$status = 'scheduled';
$by = $adminId;
$snStmt->execute();
$snStmt->close();

$nExtra = [
    ['ALL', 'Sports day practice begins from next Monday. Students must wear house uniforms.', 'Sports Department'],
    ['CLASS_8_B', 'Class 8-B maths revision class on Saturday 9 AM.', 'Karthik Venkatesh'],
    ['CLASS_9_A', 'Term 2 syllabus distribution — collect from class teacher.', 'Ramesh Babu'],
    ['88001', 'Congratulations on winning the essay competition!', 'Priya Ramachandran'],
    ['88013', 'Please submit your lab record by Wednesday.', 'Ananya Iyer'],
];
$nStmt2 = $conn->prepare('INSERT INTO notification (id, notification, sentBy, date, time, status) VALUES (?, ?, ?, ?, ?, 0)');
foreach ($nExtra as [$target, $msg, $by]) {
    $nStmt2->bind_param('sssss', $target, $msg, $by, $ndate, $ntime);
    $nStmt2->execute();
}
$nStmt2->close();

out('Seeding admin workflow & audit data...');
$entityId = tt_entity_id(9, 'A', ACADEMIC_YEAR);
$payload = json_encode(['standard' => 9, 'section' => 'A', 'academic_year' => ACADEMIC_YEAR], JSON_UNESCAPED_UNICODE);
$apReq = $conn->prepare(
    'INSERT INTO approval_requests (module, action, entity_type, entity_id, payload_json, requested_by, status) VALUES (?, ?, ?, ?, ?, ?, ?)'
);
$mod = 'timetable';
$act = 'submit';
$etype = 'class_timetable';
$reqBy = 'T006';
$pending = 'pending';
$apReq->bind_param('sssssss', $mod, $act, $etype, $entityId, $payload, $reqBy, $pending);
$apReq->execute();
$apReq->close();

$conn->query(
    "INSERT INTO audit_logs (actor_id, actor_name, module, action, entity_type, entity_id, ip_address, user_agent) VALUES
    ('{$admin['id']}', 'Lakshmi Sundaram', 'fees', 'save_structure', 'fee_structures', '8-A', '127.0.0.1', 'seed-script'),
    ('T001', 'Priya Ramachandran', 'homework', 'create', 'homeworks', '1', '127.0.0.1', 'seed-script'),
    ('T002', 'Karthik Venkatesh', 'attendance', 'mark', 'attendance', '88001', '127.0.0.1', 'seed-script')"
);

$conn->query(
    "INSERT INTO login_audit (user_id, username, status, ip_address, user_agent) VALUES
    ('{$admin['id']}', 'admin', 'success', '127.0.0.1', 'seed-script'),
    ('T001', 'priya.ramachandran', 'success', '127.0.0.1', 'seed-script'),
    ('88001', 'aditya.krishnan', 'success', '127.0.0.1', 'seed-script'),
    (NULL, 'unknown.user', 'failed', '127.0.0.1', 'seed-script')"
);

$conn->query(
    "INSERT INTO data_quality_issues (issue_type, entity_type, entity_id, issue_details, status) VALUES
    ('missing_email', 'student', '88017', 'Student Gautham S has no parent email on file.', 'open'),
    ('duplicate_phone', 'teacher', 'T003', 'Phone number shared with another staff record.', 'open'),
    ('invalid_marks', 'marks', '88004', 'Term 1 maths mark exceeds maximum for one entry.', 'resolved')"
);

$conn->query("INSERT INTO attendance_day_lock (lock_date, is_locked, lock_reason, locked_by) VALUES ('2026-06-01', 1, 'Month-end reconciliation', '{$admin['id']}')");

$conn->query(
    "INSERT INTO marks_revisions (student_id, test_name, before_json, after_json, changed_by) VALUES
    ('88002', 'Term 1', '{\"maths\":72}', '{\"maths\":78}', 'T002'),
    ('89005', 'Term 2', '{\"science\":65}', '{\"science\":70}', 'T003')"
);

$conn->query("INSERT INTO user_security (user_id, force_password_reset, failed_attempts) VALUES ('88009', 0, 0)");

out('');
out('=== Demo seed complete ===');
out('Password for ALL users: ' . DEMO_PASSWORD);
out('');
out('Admin  — username: admin               (role: Admin)');
out('Teacher — username: priya.ramachandran (role: Teacher)');
out('Teacher — username: karthik.venkatesh  (role: Teacher)');
out('Student — username: aditya.krishnan    (role: Student, Class 8-A)');
out('Student — username: dinesh.kumar       (role: Student, Class 9-A)');
out('');
out('Totals: 1 admin, ' . count($teachers) . ' teachers, ' . count($studentRoster) . ' students');
out('Classes: 8-A (12), 8-B (10), 9-A (10) | Academic year: ' . ACADEMIC_YEAR);
out('Also seeded: assessments, exams, exam_windows, marks_master/details, templates,');
out('  scheduled notifications, approval_requests, audit_logs, data_quality_issues, marks_revisions.');
