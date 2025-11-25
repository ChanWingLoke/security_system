<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

$errors = [];
$name = '';
$email = '';

/**
 * Validate password strength
 */
function validate_password_strength($password) {
    $errors = [];
    
    if (strlen($password) < 10) {
        $errors[] = "Password must be at least 10 characters long";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = "Password must contain at least one special character (!@#$%^&*)";
    }
    
    return $errors;
}

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
    } else {
        // Validate password strength
        $password_errors = validate_password_strength($password);
        $errors = array_merge($errors, $password_errors);
    }

    // Password confirmation match
    if ($password !== $confirm) {
        $errors[] = "Password and confirmation do not match.";
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
            id="password"
            class="form-control"
            required
          >

          <div class="mt-2">
              <div class="password-strength-bar">
                <div id="strength-bar" class="strength-bar-fill"></div>
              </div>
              <small id="strength-text" class="text-muted">Password strength: <span id="strength-label">None</span></small>
          </div>

          <div class="mt-2">
            <small class="text-muted d-block mb-1">Password must contain:</small>
            <ul class="password-requirements">
              <li id="req-length" class="requirement-unchecked">
                <span class="requirement-icon">✗</span> At least 10 characters
              </li>
              <li id="req-uppercase" class="requirement-unchecked">
                <span class="requirement-icon">✗</span> One uppercase letter
              </li>
              <li id="req-lowercase" class="requirement-unchecked">
                <span class="requirement-icon">✗</span> One lowercase letter
              </li>
              <li id="req-number" class="requirement-unchecked">
                <span class="requirement-icon">✗</span> One number
              </li>
              <li id="req-special" class="requirement-unchecked">
                <span class="requirement-icon">✗</span> One special character (!@#$%^&*)
              </li>
            </ul>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Confirm Password</label>
          <input
            type="password"
            name="confirm_password"
            id="confirm_password"
            class="form-control"
            required
          >
          <small id="confirm-match" class="text-muted"></small>
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

<style>
/* Password Strength Meter Styles */
.password-strength-bar {
  width: 100%;
  height: 8px;
  background-color: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
}

.strength-bar-fill {
  height: 100%;
  width: 0%;
  transition: width 0.3s ease, background-color 0.3s ease;
  border-radius: 4px;
}

.strength-bar-fill.strength-weak {
  width: 33%;
  background-color: #dc3545;
}

.strength-bar-fill.strength-medium {
  width: 66%;
  background-color: #ffc107;
}

.strength-bar-fill.strength-strong {
  width: 100%;
  background-color: #28a745;
}

/* Password Requirements Styles */
.password-requirements {
  list-style: none;
  padding-left: 0;
  margin-bottom: 0;
  font-size: 0.875rem;
}

.password-requirements li {
  margin-bottom: 0.25rem;
  transition: color 0.3s ease;
}

.requirement-unchecked {
  color: #6c757d;
}

.requirement-checked {
  color: #28a745;
  font-weight: 500;
}

.requirement-icon {
  display: inline-block;
  width: 16px;
  font-weight: bold;
}

#strength-label {
  font-weight: 600;
}

#strength-label.weak {
  color: #dc3545;
}

#strength-label.medium {
  color: #ffc107;
}

#strength-label.strong {
  color: #28a745;
}

#confirm-match.match-success {
  color: #28a745;
}

#confirm-match.match-error {
  color: #dc3545;
}
</style>

<script>
// Password Strength Checker
document.addEventListener('DOMContentLoaded', function() {
  const passwordInput = document.getElementById('password');
  const confirmInput = document.getElementById('confirm_password');
  const strengthBar = document.getElementById('strength-bar');
  const strengthLabel = document.getElementById('strength-label');
  const confirmMatch = document.getElementById('confirm-match');

  // Requirement elements
  const reqLength = document.getElementById('req-length');
  const reqUppercase = document.getElementById('req-uppercase');
  const reqLowercase = document.getElementById('req-lowercase');
  const reqNumber = document.getElementById('req-number');
  const reqSpecial = document.getElementById('req-special');

  // Check password strength
  passwordInput.addEventListener('input', function() {
    const password = this.value;
    let strength = 0;
    let strengthText = 'None';

    // Reset bar
    strengthBar.className = 'strength-bar-fill';
    strengthLabel.className = '';

    if (password.length === 0) {
      strengthLabel.textContent = 'None';
      updateRequirement(reqLength, false);
      updateRequirement(reqUppercase, false);
      updateRequirement(reqLowercase, false);
      updateRequirement(reqNumber, false);
      updateRequirement(reqSpecial, false);
      return;
    }

    // Check requirements
    const hasLength = password.length >= 10;
    const hasUppercase = /[A-Z]/.test(password);
    const hasLowercase = /[a-z]/.test(password);
    const hasNumber = /[0-9]/.test(password);
    const hasSpecial = /[^A-Za-z0-9]/.test(password);

    // Update visual checkmarks
    updateRequirement(reqLength, hasLength);
    updateRequirement(reqUppercase, hasUppercase);
    updateRequirement(reqLowercase, hasLowercase);
    updateRequirement(reqNumber, hasNumber);
    updateRequirement(reqSpecial, hasSpecial);

    // Calculate strength
    if (hasLength) strength++;
    if (hasUppercase) strength++;
    if (hasLowercase) strength++;
    if (hasNumber) strength++;
    if (hasSpecial) strength++;

    // Update strength bar and label
    if (strength <= 2) {
      strengthBar.classList.add('strength-weak');
      strengthLabel.classList.add('weak');
      strengthText = 'Weak';
    } else if (strength <= 4) {
      strengthBar.classList.add('strength-medium');
      strengthLabel.classList.add('medium');
      strengthText = 'Medium';
    } else {
      strengthBar.classList.add('strength-strong');
      strengthLabel.classList.add('strong');
      strengthText = 'Strong';
    }

    strengthLabel.textContent = strengthText;

    // Check password match
    checkPasswordMatch();
  });

  // Check confirm password match
  confirmInput.addEventListener('input', checkPasswordMatch);

  function checkPasswordMatch() {
    const password = passwordInput.value;
    const confirm = confirmInput.value;

    if (confirm.length === 0) {
      confirmMatch.textContent = '';
      confirmMatch.className = 'text-muted';
      return;
    }

    if (password === confirm) {
      confirmMatch.textContent = '✓ Passwords match';
      confirmMatch.className = 'match-success';
    } else {
      confirmMatch.textContent = '✗ Passwords do not match';
      confirmMatch.className = 'match-error';
    }
  }

  function updateRequirement(element, isMet) {
    const icon = element.querySelector('.requirement-icon');
    if (isMet) {
      element.classList.remove('requirement-unchecked');
      element.classList.add('requirement-checked');
      icon.textContent = '✓';
    } else {
      element.classList.remove('requirement-checked');
      element.classList.add('requirement-unchecked');
      icon.textContent = '✗';
    }
  }
});
</script>

<?php
render_footer();
?>
