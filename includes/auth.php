<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session timeout in seconds
define('SESSION_TIMEOUT', 300);

/**
 * Check if session has timed out due to inactivity
 */
function check_session_timeout() {
    if (isset($_SESSION['user_id'])) {
        if (isset($_SESSION['last_activity'])) {
            $inactive_time = time() - $_SESSION['last_activity'];
            
            if ($inactive_time > SESSION_TIMEOUT) {
                $user_id = $_SESSION['user_id'];
                
                session_unset();
                session_destroy();
                session_start();
                
                log_event($user_id, 'SESSION_TIMEOUT', 
                    "Session timed out after {$inactive_time} seconds of inactivity");
                header("Location: /security_system/public/login.php?timeout=1");
                exit();
            }
        }

        $_SESSION['last_activity'] = time();
    }
}

function force_logout_on_refresh() {
    if (isset($_SESSION['just_logged_in'])) {
        unset($_SESSION['just_logged_in']);
        $_SESSION['last_page'] = $_SERVER['REQUEST_URI'];
        return;
    }
    
    $current_page = $_SERVER['REQUEST_URI'];
    
    // If user is logged in, check if this is a refresh or navigation
    if (isset($_SESSION['user_id'])) {
        if (isset($_SESSION['last_page']) && $_SESSION['last_page'] === $current_page) {
            $user_id = $_SESSION['user_id'];
            
            log_event($user_id, 'SESSION_REFRESH', "User logged out due to page refresh");
            
            $_SESSION = array();
            
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            
            session_destroy();
            session_start(); // Start fresh session
            
            // Redirect to login
            header("Location: /security_system/public/login.php?refresh=1");
            exit();
        }
        
        // Update last page for next request (it's navigation, not refresh)
        $_SESSION['last_page'] = $current_page;
    }
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
    check_session_timeout();
    force_logout_on_refresh();

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
