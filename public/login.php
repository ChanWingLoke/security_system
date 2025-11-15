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
        $stmt->bind_result($id, $name, $stored_password, $role);

        if ($stmt->fetch()) {
            // 👉 Insecure plaintext check (baseline)
            if ($password === $stored_password) {
                $_SESSION['user_id']   = $id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = $role;

                log_event($id, 'LOGIN_SUCCESS', "User $email logged in successfully");

                $stmt->close();
                redirect('/security_system/public/dashboard.php');
            } else {
                $errors[] = "Invalid email or password.";
                log_event($id, 'LOGIN_FAILED', "Wrong password for $email");
            }
        } else {
            $errors[] = "Invalid email or password.";
            log_event(null, 'LOGIN_FAILED', "Login attempt for unknown email: $email");
        }

        $stmt->close();
    }
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
      </form>
    </div>
  </div>
</div>

<?php
render_footer();
