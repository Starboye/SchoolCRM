<?php
session_start();

if (!isset($_SESSION['access'], $_SESSION['id'], $_SESSION['name'])) {
    header('Location: ../index.php');
    exit;
}

$access = (int)$_SESSION['access'];
if ($access !== 2) {
    http_response_code(403);
    die('Unauthorized access');
}

$db = mysqli_connect('localhost', 'root', '', 'asimos');
if (!$db) {
    die('Database connection failed: ' . mysqli_connect_error());
}

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function admin_permissions(mysqli $db, string $userId): array {
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $perms = [];
    $sql = "SELECT rp.permission_key
            FROM user_roles ur
            JOIN role_permissions rp ON rp.role_id = ur.role_id
            WHERE ur.user_id = ?";
    if ($stmt = mysqli_prepare($db, $sql)) {
        mysqli_stmt_bind_param($stmt, 's', $userId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($res)) {
            $perms[$row['permission_key']] = true;
        }
        mysqli_stmt_close($stmt);
    }

    // fallback: access=2 gets super-admin permissions until delegation is configured
    if (!$perms) {
        $res = mysqli_query($db, "SELECT permission_key FROM permissions");
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $perms[$row['permission_key']] = true;
            }
        }
    }

    $cache = $perms;
    return $cache;
}

function has_permission(mysqli $db, string $permissionKey): bool {
    $userId = (string)$_SESSION['id'];
    $perms = admin_permissions($db, $userId);
    return isset($perms[$permissionKey]);
}

function require_permission(mysqli $db, string $permissionKey): void {
    if (!has_permission($db, $permissionKey)) {
        http_response_code(403);
        die('Forbidden: missing permission ' . e($permissionKey));
    }
}

function log_audit(mysqli $db, string $module, string $action, string $entityType, ?string $entityId, $before, $after): void {
    $actorId = (string)($_SESSION['id'] ?? '');
    $actorName = (string)($_SESSION['name'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 250);

    $beforeJson = $before === null ? null : json_encode($before, JSON_UNESCAPED_UNICODE);
    $afterJson = $after === null ? null : json_encode($after, JSON_UNESCAPED_UNICODE);

    $sql = "INSERT INTO audit_logs (actor_id, actor_name, module, action, entity_type, entity_id, before_json, after_json, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    if ($stmt = mysqli_prepare($db, $sql)) {
        mysqli_stmt_bind_param($stmt, 'ssssssssss', $actorId, $actorName, $module, $action, $entityType, $entityId, $beforeJson, $afterJson, $ip, $ua);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

function policy_requires_approval(mysqli $db, string $policyKey): bool {
    $policyKeyEsc = mysqli_real_escape_string($db, $policyKey);
    $row = mysqli_fetch_assoc(mysqli_query($db, "SELECT require_approval FROM approval_policies WHERE policy_key='$policyKeyEsc' LIMIT 1"));
    return isset($row['require_approval']) && (int)$row['require_approval'] === 1;
}

function submit_approval(mysqli $db, string $module, string $action, string $entityType, ?string $entityId, array $payload): bool {
    $requestedBy = (string)$_SESSION['id'];
    $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE);
    $sql = "INSERT INTO approval_requests (module, action, entity_type, entity_id, payload_json, requested_by)
            VALUES (?, ?, ?, ?, ?, ?)";
    if ($stmt = mysqli_prepare($db, $sql)) {
        mysqli_stmt_bind_param($stmt, 'ssssss', $module, $action, $entityType, $entityId, $payloadJson, $requestedBy);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }
    return false;
}
