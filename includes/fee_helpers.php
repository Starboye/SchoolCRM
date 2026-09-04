<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/db.php';

/**
 * Fee structure helpers — admin writes, students read.
 */
function fee_tables_ready(mysqli $conn): bool {
    return db_table_exists($conn, 'fee_structures') && db_table_exists($conn, 'student_fee_status');
}

function fee_current_academic_year(): string {
    $y = (int)date('Y');
    $m = (int)date('n');
    // Academic year starts June (common in India)
    if ($m >= 6) {
        return $y . '-' . substr((string)($y + 1), -2);
    }
    return ($y - 1) . '-' . substr((string)$y, -2);
}

function fee_format_amount($amount): string {
    return number_format((float)$amount, 2);
}

function fee_compute_total(float $term, float $special, float $tuition, float $lab): float {
    return round($term + $special + $tuition + $lab, 2);
}

/**
 * @return list<array<string,mixed>>
 */
function fee_structures_for_class(mysqli $conn, int $standard, string $section, string $academicYear = ''): array {
    if (!fee_tables_ready($conn)) {
        return [];
    }
    if ($academicYear === '') {
        $academicYear = fee_current_academic_year();
    }
    $stmt = $conn->prepare(
        'SELECT * FROM fee_structures
         WHERE standard = ? AND section = ? AND academic_year = ?
         ORDER BY term_label'
    );
    $stmt->bind_param('iss', $standard, $section, $academicYear);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows ?: [];
}

/**
 * Fee rows for a student (structure + their payment status).
 * @return list<array<string,mixed>>
 */
function fee_rows_for_student(mysqli $conn, string $studentId, int $standard, string $section, string $academicYear = ''): array {
    if (!fee_tables_ready($conn)) {
        return [];
    }
    if ($academicYear === '') {
        $academicYear = fee_current_academic_year();
    }
    $sql = 'SELECT fs.id AS fee_structure_id, fs.term_label, fs.term_fee, fs.special_fee,
                   fs.tuition_fee, fs.lab_fee, fs.total_fee, fs.academic_year,
                   COALESCE(sfs.status, "unpaid") AS payment_status,
                   COALESCE(sfs.amount_paid, 0) AS amount_paid,
                   sfs.paid_on
            FROM fee_structures fs
            LEFT JOIN student_fee_status sfs
              ON sfs.fee_structure_id = fs.id AND sfs.student_id = ?
            WHERE fs.standard = ? AND fs.section = ? AND fs.academic_year = ?
            ORDER BY fs.term_label';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('siss', $studentId, $standard, $section, $academicYear);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows ?: [];
}

function fee_ensure_student_status_rows(mysqli $conn, string $studentId, int $standard, string $section, string $academicYear, string $adminId): void {
    $structures = fee_structures_for_class($conn, $standard, $section, $academicYear);
    foreach ($structures as $fs) {
        $fid = (int)$fs['id'];
        $stmt = $conn->prepare(
            'INSERT IGNORE INTO student_fee_status (student_id, fee_structure_id, status, updated_by)
             VALUES (?, ?, "unpaid", ?)'
        );
        $stmt->bind_param('sis', $studentId, $fid, $adminId);
        $stmt->execute();
        $stmt->close();
    }
}

function fee_init_class_student_status(mysqli $conn, int $standard, string $section, string $academicYear, string $adminId): int {
    $count = 0;
    $stmt = $conn->prepare('SELECT id FROM student_info WHERE standard = ? AND section = ?');
    $stmt->bind_param('is', $standard, $section);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        fee_ensure_student_status_rows($conn, (string)$row['id'], $standard, $section, $academicYear, $adminId);
        $count++;
    }
    $stmt->close();
    return $count;
}

function fee_status_badge_class(string $status): string {
    return match ($status) {
        'paid' => 'success',
        'partial' => 'warning',
        default => 'danger',
    };
}
