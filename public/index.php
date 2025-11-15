<?php
require_once __DIR__ . '/../includes/layout.php';
render_header("Home - Security System");
?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="app-card-soft p-5 mb-4 text-center">
      <p class="hero-kicker mb-2">Security System Baseline</p>
      <h1 class="hero-title">Ticket Service System</h1>
      <p class="hero-subtitle mb-4">
        This is the <strong>insecure baseline version</strong> of your project
        (no password hashing, weak validation, basic logging).
        You will harden and document all security improvements later.
      </p>

      <div class="d-flex justify-content-center gap-3">
        <a class="btn btn-primary btn-lg btn-pill px-4" href="login.php">
          Login
        </a>
        <a class="btn btn-outline-secondary btn-lg btn-pill px-4" href="register.php">
          Register
        </a>
      </div>
    </div>

    <div class="mt-3 text-center text-muted small">
      <span class="me-2">Designed for:</span>
      <span class="badge rounded-pill text-bg-light border">RBAC</span>
      <span class="badge rounded-pill text-bg-light border">Input Validation</span>
      <span class="badge rounded-pill text-bg-light border">Secure Communication</span>
      <span class="badge rounded-pill text-bg-light border">Logging &amp; Testing</span>
    </div>
  </div>
</div>

<?php
render_footer();
