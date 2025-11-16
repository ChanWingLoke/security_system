<?php
require_once __DIR__ . '/../includes/db.php';

// Start session manually before auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];
$email = '';

if (!empty($_SESSION['user_id'])) {
    redirect('/security_system/public/dashboard.php');
}

// Configuration
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes in seconds

/**
 * Get client IP address
 */
function get_client_ip() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    // If behind a proxy, try to get real IP (be cautious with X-Forwarded-For in production)
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ips[0]);
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

/**
 * Check if login attempts are locked
 */
function is_login_locked($conn, $email, $ip) {
    $lockout_time = LOCKOUT_TIME; 
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as attempt_count, 
            MAX(attempted_at) as last_attempt,
            TIMESTAMPDIFF(SECOND, MAX(attempted_at), NOW()) AS time_since_last_attempt 
        FROM login_attempts
        WHERE (email = ? OR ip_address = ?)
          AND attempted_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
    ");
    $stmt->bind_param("ssi", $email, $ip, $lockout_time);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if ($row['attempt_count'] >= MAX_LOGIN_ATTEMPTS) {
        // Use the elapsed time calculated by MySQL
        $time_elapsed = (int)($row['time_since_last_attempt'] ?? 0); 
        
        // Calculate remaining lockout time (will now be correct)
        $remaining = LOCKOUT_TIME - $time_elapsed; 
        debug_to_console($remaining);
        
        // ... (rest of the return statement)
        return [
            'locked' => true,
            'remaining_time' => max(0, $remaining)
        ];
    }
    
    return ['locked' => false, 'remaining_time' => 0];
}

// Source - https://stackoverflow.com/a
// Posted by Senador, modified by community. See post 'Timeline' for change history
// Retrieved 2025-11-16, License - CC BY-SA 4.0

function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}

/**
 * Record failed login attempt
 */
function record_failed_attempt($conn, $email, $ip) {
    $stmt = $conn->prepare("
        INSERT INTO login_attempts (email, ip_address, attempted_at)
        VALUES (?, ?, NOW())
    ");
    $stmt->bind_param("ss", $email, $ip);
    $stmt->execute();
    $stmt->close();
}

/**
 * Clear login attempts after successful login
 */
function clear_login_attempts($conn, $email, $ip) {
    $stmt = $conn->prepare("
        DELETE FROM login_attempts
        WHERE email = ? OR ip_address = ?
    ");
    $stmt->bind_param("ss", $email, $ip);
    $stmt->execute();
    $stmt->close();
}

/**
 * Clean old login attempts (optional, for housekeeping)
 */
function cleanup_old_attempts($conn) {
    $conn->query("
        DELETE FROM login_attempts
        WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $ip       = get_client_ip();
    
    // Check if account is locked
    $lock_status = is_login_locked($conn, $email, $ip);
    
    if ($lock_status['locked']) {
        $minutes = ceil($lock_status['remaining_time'] / 60);
        $errors[] = "Too many failed login attempts. Please try again in {$minutes} minute(s).";
        log_event(null, 'LOGIN_BLOCKED', "Login blocked for $email from IP $ip due to rate limiting");
    } else {
        // Validate input
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email is required.";
        }
        if ($password === '') {
            $errors[] = "Password is required.";
        }
        
        if (empty($errors)) {
        $stmt = $conn->prepare("
            SELECT id, name, password, role
            FROM users
            WHERE email = ?
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($id, $name, $stored_hash, $role);

        if ($stmt->fetch()) {
            // 👉 Insecure plaintext check (baseline)
            if ($password === $stored_hash) {
                $stmt->close(); // Close before setting session
                
                // Completely clear any existing session data
                $_SESSION = array();
                
                // Set NEW session variables
                $_SESSION['user_id']   = $id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = $role;
                $_SESSION['last_activity'] = time();
                $_SESSION['just_logged_in'] = true;
                
                // Clear failed attempts
                clear_login_attempts($conn, $email, $ip);
                    
                log_event($id, 'LOGIN_SUCCESS', "User $email logged in successfully from IP $ip");

                // Load auth functions for logging only
                require_once __DIR__ . '/../includes/auth.php';
                log_event($id, 'LOGIN_SUCCESS', "User $email logged in successfully");

                // Redirect
                header("Location: /security_system/public/dashboard.php");
                exit();
                } else {
                    // Failed login
                    record_failed_attempt($conn, $email, $ip);
                    $errors[] = "Invalid email or password.";
                    log_event($id, 'LOGIN_FAILED', "Wrong password for $email from IP $ip");
                }
            } else {
                $stmt->close();
                
                // Unknown email
                record_failed_attempt($conn, $email, $ip);
                $errors[] = "Invalid email or password.";
                require_once __DIR__ . '/../includes/auth.php';
                log_event($id, 'LOGIN_FAILED', "Failed login attempt for email: $email");
            }
        } else {
            $errors[] = "Invalid email or password.";
            require_once __DIR__ . '/../includes/auth.php';
            log_event(null, 'LOGIN_FAILED', "Login attempt for unknown email: $email from IP $ip");
        }
    }
    
    // Periodic cleanup (runs on 1% of requests)
    if (rand(1, 100) === 1) {
        cleanup_old_attempts($conn);
    }
}

// NOW load auth.php after POST handling
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

// Check for logout messages
if (isset($_GET['timeout']) && $_GET['timeout'] == '1') {
    $errors[] = "Your session has expired due to inactivity. Please login again.";
}

if (isset($_GET['refresh']) && $_GET['refresh'] == '1') {
    $errors[] = "Please login again to continue.";
}

// If already logged in, redirect
if (!empty($_SESSION['user_id']) && empty($errors)) {
    redirect('/security_system/public/dashboard.php');
}

render_header("Login - Security System");
?>
<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <div class="app-card p-4">
      <h2 class="app-section-title mb-3 text-center">Welcome back</h2>
      <p class="text-muted text-center mb-4">
        Sign in to manage your tickets and track their status.
      </p>
      
      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <ul class="mb-0">
            <?php foreach ($errors as $e): ?>
              <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
      
      <form method="post" novalidate>
        <div class="mb-3">
          <label class="form-label fw-semibold">Email</label>
          <input
            type="email"
            name="email"
            class="form-control"
            value="<?= htmlspecialchars($email) ?>"
            required
          >
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Password</label>
          <input
            type="password"
            name="password"
            class="form-control"
            required
          >
        </div>
        <button type="submit" class="btn btn-primary w-100 btn-pill mb-3">
          Login
        </button>
        <p class="text-center text-muted mb-0">
          Don't have an account?
          <a href="register.php">Register here</a>.
        </p>
      </div>
    </div>
  </div>
</div>
<?php
render_footer();
?>