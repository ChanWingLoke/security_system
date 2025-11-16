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

    // Basic validation (you can strengthen later)
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

    // Password policy checks
    if ($password !== '') {
        // Minimum length (you can pick 8 or 10)
        if (strlen($password) < 10) {
            $errors[] = "Password must be at least 10 characters long.";
        }

        // At least one uppercase
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter (A-Z).";
        }

        // At least one lowercase
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter (a-z).";
        }

        // At least one digit
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one digit (0-9).";
        }

        // At least one special character
        if (!preg_match('/[\W_]/', $password)) {
            $errors[] = "Password must contain at least one special character (e.g. !@#\$%^&*).";
        }
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
        $role = 'user'; // default role

        // ✅ HASH THE PASSWORD HERE
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            INSERT INTO users (name, email, password, role)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("ssss", $name, $email, $password_hash, $role);
        $stmt->execute();

        if ($stmt->error) {
            $errors[] = "Registration failed: " . $stmt->error;
        } else {
            $new_id = $stmt->insert_id;

            // Log registration if logger exists
            if (function_exists('log_event')) {
                log_event($new_id, 'USER_REGISTERED', "Registered with email: $email");
            }

            // Auto-login
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
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h2 class="mb-4 text-center">Register</h2>

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
            <label class="form-label">Name</label>
            <input
              type="text"
              name="name"
              class="form-control"
              value="<?= htmlspecialchars($name) ?>"
              required
            >
          </div>
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
            <div class="form-text">
              Must be at least 10 characters, with uppercase, lowercase, number and special character.
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input
              type="password"
              name="confirm_password"
              class="form-control"
              required
            >
          </div>
          <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>

        <p class="mt-3 text-center">
          Already have an account?
          <a href="login.php">Login here</a>.
        </p>
      </div>
    </div>
  </div>
</div>

<?php
render_footer();
