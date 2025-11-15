<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

$errors = [];
$email = '';

if (!empty($_SESSION['user_id'])) {
    redirect('/security_system/public/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

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
            // ✅ SECURE CHECK WITH HASH
            if (password_verify($password, $stored_hash)) {
                $_SESSION['user_id']   = $id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = $role;

                if (function_exists('log_event')) {
                    log_event($id, 'LOGIN_SUCCESS', "User $email logged in successfully");
                }

                $stmt->close();
                redirect('/security_system/public/dashboard.php');
            } else {
                $errors[] = "Invalid email or password.";
                if (function_exists('log_event')) {
                    log_event($id, 'LOGIN_FAILED', "Wrong password for $email");
                }
            }
        } else {
            $errors[] = "Invalid email or password.";
            if (function_exists('log_event')) {
                log_event(null, 'LOGIN_FAILED', "Login attempt for unknown email: $email");
            }
        }

        $stmt->close();
    }
}

render_header("Login - Security System");
?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h2 class="mb-4 text-center">Login</h2>

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
            <label class="form-label">Email</label>
            <input
              type="email"
              name="email"
              class="form-control"
              value="<?= htmlspecialchars($email) ?>"
              required
            >
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input
              type="password"
              name="password"
              class="form-control"
              required
            >
          </div>
          <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>

        <p class="mt-3 text-center">
          Don't have an account?
          <a href="register.php">Register here</a>.
        </p>
      </div>
    </div>
  </div>
</div>

<?php
render_footer();
