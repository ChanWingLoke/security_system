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
    $privacy_accepted = isset($_POST['privacy_policy']) ? 1 : 0;

    // --- Basic validation ---

    if ($name === '') {
        $errors[] = "Name is required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }

    // Password required
    if ($password === '') {
        $errors[] = "Password is required.";
    }

    // Password confirmation match
    if ($password !== $confirm) {
        $errors[] = "Password and confirmation do not match.";
    }

    // Password policy checks
    if ($password !== '') {
        if (strlen($password) < 10) {
            $errors[] = "Password must be at least 10 characters long.";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter (A-Z).";
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter (a-z).";
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one digit (0-9).";
        }
        if (!preg_match('/[\W_]/', $password)) {
            $errors[] = "Password must contain at least one special character (e.g. !@#\$%^&*).";
        }
    }

    // Privacy policy agreement
    if (!$privacy_accepted) {
        $errors[] = "You must agree to the Data Privacy & Security Policy to create an account.";
    }

    // Check duplicate email
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if ($stmt === false) {
            $errors[] = "Database error (prepare failed on email check).";
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors[] = "Email is already registered.";
            }
            $stmt->close();
        }
    }

    // Insert user if no errors
    if (empty($errors)) {
        $role = 'user';
        
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $privacy_accepted = $privacy_accepted ? 1 : 0;

        $stmt = $conn->prepare("
            INSERT INTO users (name, email, password, role, privacy_accepted, privacy_accepted_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        if ($stmt === false) {
            // If you still hit errors, this message will show instead of fatal crash
            $errors[] = "Database error (prepare failed on insert): " . $conn->error;
        } else {
            $stmt->bind_param("ssssi", $name, $email, $password_hash, $role, $privacy_accepted);
            $stmt->execute();

            if ($stmt->error) {
                $errors[] = "Registration failed: " . $stmt->error;
            } else {
                $new_id = $stmt->insert_id;

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
          <div class="mb-3 form-check">
            <input
              type="checkbox"
              class="form-check-input"
              id="privacy_policy"
              name="privacy_policy"
              value="1"
              <?= !empty($_POST['privacy_policy']) ? 'checked' : '' ?>
            >
            <label class="form-check-label" for="privacy_policy">
              I have read and agree to the
              <a href="/security_system/public/security_tips.php?popup=1"
                onclick="window.open(
                    '/security_system/public/security_tips.php?popup=1',
                    'PrivacyPolicy',
                    'width=800,height=600,top=100,left=200'
                ); return false;">
                Data Privacy &amp; Security Policy
              </a>
            </label>
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
