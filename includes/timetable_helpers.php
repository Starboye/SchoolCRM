<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/db.php';

function tt_tables_ready(mysqli $conn): bool {
    return db_table_exists($conn, 'timetable_slots')
        && db_table_exists($conn, 'class_teacher_assignments')
        && db_table_exists($conn, 'class_timetables');
}

function tt_current_academic_year(): string {
    $y = (int)date('Y');
    $m = (int)date('n');
    if ($m >= 6) {
        return $y . '-' . substr((string)($y + 1), -2);
    }
    return ($y - 1) . '-' . substr((string)$y, -2);
}

function tt_entity_id(int $standard, string $section, string $year): string {
    return $standard . '_' . strtoupper($section) . '_' . $year;
}

function tt_parse_entity_id(string $entityId): ?array {
    $parts = explode('_', $entityId, 3);
    if (count($parts) !== 3) {
        return null;
    }
    return [
        'standard' => (int)$parts[0],
        'section' => $parts[1],
        'academic_year' => $parts[2],
    ];
}

/**
 * @return list<array<string,mixed>>
 */
function tt_classes_for_teacher(mysqli $conn, string $teacherId, string $year = ''): array {
    if (!tt_tables_ready($conn)) {
        return [];
    }
    if ($year === '') {
        $year = tt_current_academic_year();
    }
    $stmt = $conn->prepare(
        'SELECT cs.standard, cs.section, cs.academic_year, cs.class_teacher_id,
                COALESCE(ct.status, "draft") AS status, ct.review_note
         FROM class_teacher_assignments cs
         LEFT JOIN class_timetables ct
           ON ct.standard = cs.standard AND ct.section = cs.section AND ct.academic_year = cs.academic_year
         WHERE cs.class_teacher_id = ? AND cs.academic_year = ?
         ORDER BY cs.standard, cs.section'
    );
    $stmt->bind_param('ss', $teacherId, $year);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows ?: [];
}

function tt_get_status(mysqli $conn, int $standard, string $section, string $year = ''): string {
    if (!tt_tables_ready($conn)) {
        return 'draft';
    }
    if ($year === '') {
        $year = tt_current_academic_year();
    }
    $stmt = $conn->prepare(
        'SELECT status FROM class_timetables WHERE standard = ? AND section = ? AND academic_year = ? LIMIT 1'
    );
    $stmt->bind_param('iss', $standard, $section, $year);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? (string)$row['status'] : 'draft';
}

function tt_is_class_teacher(mysqli $conn, string $teacherId, int $standard, string $section, string $year = ''): bool {
    if (!tt_tables_ready($conn)) {
        return false;
    }
    if ($year === '') {
        $year = tt_current_academic_year();
    }
    $stmt = $conn->prepare(
        'SELECT 1 FROM class_teacher_assignments
         WHERE standard = ? AND section = ? AND academic_year = ? AND class_teacher_id = ? LIMIT 1'
    );
    $stmt->bind_param('isss', $standard, $section, $year, $teacherId);
    $stmt->execute();
    $ok = (bool)$stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $ok;
}

function tt_teacher_can_edit(mysqli $conn, string $teacherId, int $standard, string $section, string $year = ''): bool {
    if (!tt_is_class_teacher($conn, $teacherId, $standard, $section, $year)) {
        return false;
    }
    $status = tt_get_status($conn, $standard, $section, $year);
    return in_array($status, ['draft', 'rejected'], true);
}

function tt_is_published(mysqli $conn, int $standard, string $section, string $year = ''): bool {
    return tt_get_status($conn, $standard, $section, $year) === 'approved';
}

/**
 * @return array{grid: array<int,array<int,string>>, maxPeriod: int}
 */
function tt_grid_for_class(mysqli $conn, int $standard, string $section, bool $approvedOnly = false, string $year = ''): array {
    $grid = [];
    $maxPeriod = 0;
    if (!db_table_exists($conn, 'timetable_slots')) {
        return ['grid' => $grid, 'maxPeriod' => 0];
    }
    if ($year === '') {
        $year = tt_current_academic_year();
    }
    if ($approvedOnly && tt_tables_ready($conn) && !tt_is_published($conn, $standard, $section, $year)) {
        return ['grid' => $grid, 'maxPeriod' => 0];
    }
    $stmt = $conn->prepare(
        'SELECT day_of_week, period_no, subject_name FROM timetable_slots
         WHERE standard = ? AND section = ? ORDER BY period_no, day_of_week'
    );
    $stmt->bind_param('is', $standard, $section);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($slot = $res->fetch_assoc()) {
        $p = (int)$slot['period_no'];
        $d = (int)$slot['day_of_week'];
        $grid[$p][$d] = (string)$slot['subject_name'];
        if ($p > $maxPeriod) {
            $maxPeriod = $p;
        }
    }
    $stmt->close();
    return ['grid' => $grid, 'maxPeriod' => $maxPeriod];
}

function tt_day_labels(): array {
    return [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday'];
}

function tt_status_badge_class(string $status): string {
    return match ($status) {
        'approved' => 'success',
        'pending' => 'warning',
        'rejected' => 'danger',
        default => 'secondary',
    };
}

function tt_upsert_class_status(mysqli $conn, int $standard, string $section, string $year, string $status, ?string $teacherId = null): void {
    $stmt = $conn->prepare(
        'INSERT INTO class_timetables (standard, section, academic_year, status, submitted_by, submitted_at)
         VALUES (?, ?, ?, ?, ?, NOW())
         ON DUPLICATE KEY UPDATE status = VALUES(status),
         submitted_by = COALESCE(VALUES(submitted_by), submitted_by),
         submitted_at = IF(VALUES(status) = "pending", NOW(), submitted_at)'
    );
    $stmt->bind_param('issss', $standard, $section, $year, $status, $teacherId);
    $stmt->execute();
    $stmt->close();
}

function tt_save_class_grid(mysqli $conn, int $standard, string $section, string $teacherId, array $cells, string $adminId = ''): int {
    $createdBy = $adminId !== '' ? $adminId : $teacherId;
    $del = $conn->prepare('DELETE FROM timetable_slots WHERE standard = ? AND section = ?');
    $del->bind_param('is', $standard, $section);
    $del->execute();
    $del->close();

    $ins = $conn->prepare(
        'INSERT INTO timetable_slots (teacher_id, standard, section, subject_name, day_of_week, period_no, created_by)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $count = 0;
    foreach ($cells as $period => $days) {
        foreach ($days as $day => $subject) {
            $subject = trim((string)$subject);
            if ($subject === '') {
                continue;
            }
            $p = (int)$period;
            $d = (int)$day;
            $ins->bind_param('sissiis', $teacherId, $standard, $section, $subject, $d, $p, $createdBy);
            $ins->execute();
            $count++;
        }
    }
    $ins->close();
    return $count;
}

function tt_process_approval(mysqli $conn, string $entityId, string $decision, string $reviewerId, string $note = ''): bool {
    $parsed = tt_parse_entity_id($entityId);
    if (!$parsed || !tt_tables_ready($conn)) {
        return false;
    }
    $status = $decision === 'approved' ? 'approved' : 'rejected';
    $stmt = $conn->prepare(
        'UPDATE class_timetables SET status = ?, reviewed_by = ?, reviewed_at = NOW(), review_note = ?
         WHERE standard = ? AND section = ? AND academic_year = ?'
    );
    $std = (int)$parsed['standard'];
    $sec = (string)$parsed['section'];
    $year = (string)$parsed['academic_year'];
    $stmt->bind_param('ssssis', $status, $reviewerId, $note, $std, $sec, $year);
    $ok = $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    if ($ok && $affected === 0) {
        tt_upsert_class_status($conn, $std, $sec, $year, $status);
        $stmt2 = $conn->prepare(
            'UPDATE class_timetables SET reviewed_by = ?, reviewed_at = NOW(), review_note = ?
             WHERE standard = ? AND section = ? AND academic_year = ?'
        );
        $stmt2->bind_param('sssis', $reviewerId, $note, $std, $sec, $year);
        $stmt2->execute();
        $stmt2->close();
    }
    return $ok;
}
