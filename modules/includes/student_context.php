<?php
declare(strict_types=1);

/**
 * Load shared student header variables (row, notifications).
 */
function student_portal_context(): array {
    $conn = db_mysqli();
    $id = (string)$_SESSION['id'];
    $username = (string)$_SESSION['name'];
    $row = [];

    $stmt = $conn->prepare('SELECT * FROM student_info WHERE id = ? LIMIT 1');
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
    }
    $stmt->close();

    $notification = [];
    $classKey = '';
    if (!empty($row['standard']) && !empty($row['section'])) {
        $classKey = strtoupper('CLASS_' . $row['standard'] . '_' . $row['section']);
    }
    $nSql = "SELECT notification, sentBy FROM notification
             WHERE TRIM(id) = ? OR TRIM(id) = 'ALL'" . ($classKey !== '' ? " OR TRIM(id) = ?" : '') . "
             ORDER BY date DESC, time DESC LIMIT 15";
    $nStmt = $conn->prepare($nSql);
    if ($classKey !== '') {
        $nStmt->bind_param('ss', $id, $classKey);
    } else {
        $nStmt->bind_param('s', $id);
    }
    $nStmt->execute();
    $nRes = $nStmt->get_result();
    while ($n = $nRes->fetch_assoc()) {
        $notification[(string)$n['sentBy']] = (string)$n['notification'];
    }
    $nStmt->close();
    if ($notification === []) {
        $notification = ['System' => 'No notifications'];
    }

    return [
        'conn' => $conn,
        'id' => $id,
        'username' => $username,
        'row' => $row,
        'notification' => $notification,
    ];
}

function student_partials_dir(): string {
    return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'partials';
}

function student_layout_start(string $pageTitle, bool $withCharts = false): void {
    $GLOBALS['pageTitle'] = $pageTitle;
    $GLOBALS['withCharts'] = $withCharts;
    $partialsRoot = student_partials_dir();
    include $partialsRoot . DIRECTORY_SEPARATOR . 'student_head.php';
    include $partialsRoot . DIRECTORY_SEPARATOR . 'header.php';
    include $partialsRoot . DIRECTORY_SEPARATOR . 'sidebar.php';
}

function student_layout_end(bool $withCharts = false): void {
    $GLOBALS['withCharts'] = $withCharts;
    include student_partials_dir() . DIRECTORY_SEPARATOR . 'student_footer.php';
}
