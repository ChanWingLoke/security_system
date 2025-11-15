<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Simple redirect helper
 */
function redirect($path) {
    header("Location: $path");
    exit;
}

/**
 * Require a logged-in user
 */
function require_login() {
    if (empty($_SESSION['user_id'])) {
        redirect('/security_system/public/login.php');
    }
}

/**
 * Require admin role
 */
function require_admin() {
    require_login();
    if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        echo "Forbidden: Admins only.";
        exit;
    }
}

// ⚠ Insecure logger (no sanitisation, no prepared statements)
// You will improve this later.
function log_event($user_id, $action, $details = null) {
    // we need DB connection
    require __DIR__ . '/db.php';

    // Basic string concat (intentionally bad, SQL injection possible via $details)
    $uid = $user_id === null ? "NULL" : (int)$user_id;
    $details_sql = $details === null ? "NULL" : "'" . $conn->real_escape_string($details) . "'";

    $sql = "
        INSERT INTO audit_logs (user_id, action, details)
        VALUES ($uid, '$action', $details_sql)
    ";

    $conn->query($sql);
}
