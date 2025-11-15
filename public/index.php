<?php
require_once __DIR__ . '/../includes/layout.php';
render_header("Home - Security System");
?>

<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="p-5 bg-white rounded shadow-sm">
      <h1 class="mb-3 text-center">Security System (Baseline)</h1>
      <p class="text-muted text-center">
        This is the insecure baseline version (no password hashing yet).
        You will later improve and document the security features.
      </p>
      <div class="d-flex justify-content-center gap-3">
        <a class="btn btn-primary" href="login.php">Login</a>
        <a class="btn btn-outline-secondary" href="register.php">Register</a>
      </div>
    </div>
  </div>
</div>

<?php
render_footer();
