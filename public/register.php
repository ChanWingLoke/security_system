<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($name === '') {
        $errors[] = "Name is required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }

    if ($password === '') {
        $errors[] = "Password is required.";
    }

    if ($password !== $confirm) {
        $errors[] = "Password and confirmation do not match.";
    }

    // Check duplicate email
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Email is already registered.";
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $role = 'user'; // default

        // 👉 Intentionally storing plaintext password for baseline
        $stmt = $conn->prepare("
            INSERT INTO users (name, email, password, role)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("ssss", $name, $email, $password, $role);
        $stmt->execute();

        if ($stmt->error) {
            $errors[] = "Registration failed: " . $stmt->error;
        } else {
            $new_id = $stmt->insert_id;

            log_event($new_id, 'USER_REGISTERED', "Registered with email: $email");

            $_SESSION['user_id']   = $new_id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = $role;

            $stmt->close();
            redirect('/security_system/public/dashboard.php');
        }
        $stmt->close();
    }
}

render_header("Register - Security System");
?>

<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <div class="app-card p-4">
      <h2 class="app-section-title mb-3 text-center">Create an Account</h2>
      <p class="text-muted text-center mb-4">
        Sign up to submit and track support tickets.
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
          <label class="form-label fw-semibold">Name</label>
          <input
            type="text"
            name="name"
            class="form-control"
            value="<?= htmlspecialchars($name) ?>"
            required
          >
        </div>

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

        <div class="mb-3">
          <label class="form-label fw-semibold">Confirm Password</label>
          <input
            type="password"
            name="confirm_password"
            class="form-control"
            required
          >
        </div>

        <button type="submit" class="btn btn-primary w-100 btn-pill mb-3">
          Create Account
        </button>

        <p class="text-center text-muted mb-0">
          Already have an account?
          <a href="login.php">Login here</a>.
        </p>
      </form>
    </div>
  </div>
</div>

<?php
render_footer();
